<?php
/**
 * PHPOnix, an Onix parser and importer 
 *
 * @category	PHPOnix
 * @package	PHPOnix_ParserSubject
 * @copyright   Bruno Rossi <brunorossiweb@gmail.com>
 * @license BSD
 */

/**
 * PHPOnix_ParserSubject
 *
 * @category	PHPOnix
 * @package	PHPOnix_ParserSubject
 */
class PHPOnix_ParserSubject
implements SplSubject
{
    /**
     *
     * @var type 
     */
    protected $_parser;
    
    /**
     * An array that represents the pointers to the start of the <Header> tag and to
     * the end of the </Header> tag. Example:
     * 
     * array(
     *  'start' => integer that represents the position of the beginning char of the <Header> tag into the xml 
     *  'end' => integer that represents the position of the ending char of the </Header> tag into the xml 
     * )
     * 
     * @var array
     */
    protected $_headerTagCharPointers = array();
    
    /**
     * An array that represents the pointers to the start of the <Product> tag and to
     * the end of the </Product> tag. Example:
     * 
     * array(
     *  // First Product 
     *  0 => array(
     *      'start' => integer that represents the position of the beginning char of the <Product> tag into the xml 
     *      'end' => integer that represents the position of the ending char of the </Product> tag into the xml 
     *  ),
     *  // Second Product
     *  ...
     *  ...
     * );
     * 
     * @var array
     */
    protected $_productTagCharPointers = array();
    
    /**
     * The number of Product Tag occourrences
     * 
     * @var int 
     */
    protected $_productTagOccourrences = 0;
    
    /**
     * The current tag name that we notify to observers
     * 
     * @var string
     */
    protected $_currentTagName;
    
    /**
     * The current content that we notify to the observers
     * 
     * Must be a valid xml string
     * 
     * @var string
     */
    protected $_currentContent;
    
    /**
     *
     * @var type 
     */
    protected $_observers = array();
    
    /**
     *
     * @var type 
     */
    protected $_xml;
    
    /**
     * Uses the XML Parse PHP functions to construct a SAX like parser object
     * It is useful to parse long Onix XML files without loading the entire
     * DOM into memory. The Header and Product Tag are loaded one by one and
     * then parsed one by one via Observers objects.
     */
    public function __construct() 
    {
        $this->_parser = xml_parser_create("UTF-8");
        xml_set_object($this->_parser, $this);
        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, false);
        xml_parser_set_option($this->_parser, XML_OPTION_SKIP_WHITE, true);
        xml_set_element_handler($this->_parser, "_openTag", "_closeTag");
    }

    /**
     * Starts the xml parsing
     * 
     * @param string $xml a string that represents the xml to parse
     */
    public function parse($xml) 
    {
        $this->_xml = $xml;
        xml_parse($this->_parser, $this->_xml);
        xml_parser_free($this->_parser);
    }

    /**
     * Triggered by the parser when it founds an open tag
     * 
     * It saves the start char pointers for the product and header tag.
     * The pointers are saved into an associative array as follow:
     * array(
     *    'start' => int that represents the pointer to the open tag, 
     *    'end' 0 => int taht represents the pointer to the close tag,
     * );
     * 
     * @param object $parser
     * @param string $tagName the name of the tag
     * @param array $attributes the attributes of the tag
     */
    private function _openTag($parser, $tagName, $attributes) 
    {
        
        if ('Product' === $tagName) {
                        
            $offset = 0;
            
            if (true === isset($this->_productTagCharPointers[($this->_productTagOccourrences - 1)])) {
                $offset = $this->_productTagCharPointers[($this->_productTagOccourrences - 1)]['end'];  
            }
            
            $this->_productTagCharPointers[$this->_productTagOccourrences]['start'] = strpos($this->_xml, '<Product>', $offset);

        } else if ('Header' === $tagName) {
            
            $this->_headerTagCharPointers['start'] = strpos($this->_xml, '<Header>', 0);
        
        }
        
    }

    /**
     * Triggered by the parser when it founds a close tag
     * 
     * It saves the end char pointers for the product and header tag
     * The pointers are saved into an associative array as follow:
     * array(
     *    'start' => int that represents the pointer to the open tag, 
     *    'end' 0 => int taht represents the pointer to the close tag,
     * );
     * 
     * Increments the product tag occourences
     * 
     * Loads the current content for each tag using the char pointers to slice the input xml string
     *
     * Notifies the tag ending to all attached observers
     * 
     * @param object $parser
     * @param string $tagName the tag name
     */
    private function _closeTag($parser, $tagName) 
    {
        
        $this->_currentTagName = $tagName;        
        
        if ('Product' === $tagName) {
            
            $offset = $this->_productTagCharPointers[$this->_productTagOccourrences]['start'];  
            
            $this->_productTagCharPointers[$this->_productTagOccourrences]['end'] = strpos($this->_xml, '</Product>', $offset) + strlen('</Product>');
            
            $length = $this->_productTagCharPointers[$this->_productTagOccourrences]['end'] - $this->_productTagCharPointers[$this->_productTagOccourrences]['start'];
            
            $this->_currentContent = substr($this->_xml, $this->_productTagCharPointers[$this->_productTagOccourrences]['start'], $length);
            
            ++$this->_productTagOccourrences;  
            
            $this->notify();    
        
        }  else if ('Header' === $tagName) {
            
            $this->_headerTagCharPointers['end'] = strpos($this->_xml, '</Header>', 0) + strlen('</Header>');

            $length = $this->_headerTagCharPointers['end'] - $this->_headerTagCharPointers['start'];
            
            $this->_currentContent = substr($this->_xml, $this->_headerTagCharPointers['start'], $length);
            
            $this->notify();    
        }
                    
    }
    
    /**
     * 
     * @return int
     */
    public function getProductTagOccourrences()
    {
        return $this->_productTagOccourrences;
    }

    /**
     * 
     * @return array
     */
    public function getHeaderTagCharPointers() 
    {
        return $this->_headerTagCharPointers;
    }

    /**
     * 
     * @return array
     */
    public function getProductTagCharPointers() 
    {
        return $this->_productTagCharPointers;
    }
    
    /**
     * 
     * @return array
     */
    public function getCurrentTagName() 
    {
        return $this->_currentTagName;
    }

    /**
     * 
     * @return string
     */
    public function getCurrentContent() 
    {
        return $this->_currentContent;
    }
    
    /**
     * Updates all the attached observer
     */
    public function notify() 
    {
        foreach ($this->_observers as $observer) {
            $observer->update($this);
        }
    }
    
    /**
     * Attachs an observer
     * 
     * @param SplObserver $observer
     * @return PHPOnix_ParserSubject
     */
    public function attach(SplObserver $observer) 
    {
        $this->_observers[] = $observer;
        return $this;
    }
    
    /**
     * Detach an observer
     * 
     * @param SplObserver $observer
     * @return PHPOnix_ParserSubject
     */
    public function detach(SplObserver $observer) 
    {
        $this->observers = array_diff($this->_observers, array($observer));
        return $this;
    }
    
}