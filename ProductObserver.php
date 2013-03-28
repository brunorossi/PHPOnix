<?php
/**
 * PHPOnix, an Onix parser and importer 
 *
 * @category	PHPOnix
 * @package	PHPOnix_ProductObserver
 * @copyright   Bruno Rossi <brunorossiweb@gmail.com>
 * @license BSD
 */

/**
 * PHPOnix_ProductObserver
 *
 * @category	PHPOnix
 * @package	PHPOnix_ProductObserver
 */
class PHPOnix_ProductObserver 
implements SplObserver 
{
    
    /**
     * The collection of parser plugins for the 
     * Prodcut Tag
     * 
     * You can inject into this Product Observer
     * a collection with an arbitrary number of 
     * plugins to parse various parts of the 
     * Product Tag
     * 
     * @var PHPOnix_Domain_ParserCollectionInterface 
     */
    protected $_parserCollection = null;
    
    /**
     * The collection of importer plugins for the 
     * Prodcut Tag
     * You can inject a collection with 
     * an arbitrary number of plugins to
     * import various parts of the product tag
     * or to handle the import into various
     * data source (databases, csv, etc.)
     * 
     * @var PHPOnix_Domain_ImporterCollectionInterface 
     */    
    protected $_importerCollection = null;
    
    /**
     * Parses the Product Tag with the parser plugins placed into the parser plugin collection
     * 
     * @param SimpleXMLElement $xmlObject a simple xml element that represents the Product Tag 
     * @return array an array that contains the parsed results
     */
    protected function _parse(SimpleXMLElement $xmlObject) 
    {
        $item = array();
        if (null !== $this->_parserCollection) {
            foreach ($this->_parserCollection as $namespace => $parser) {
                $item[$namespace] = $parser->parse($xmlObject);
            }
        }
        return $item;
    }
    
    /**
     * Imports the Product Tag parsed results using the importer plugins registered into 
     * the importer plugin collection 
     * 
     * @param array $item the array that contains the parsed values
     */
    protected function _import(array $item) 
    {
        if (null !== $this->_importerCollection) {
            foreach ($this->_importerCollection as $importer) {
                $importer->import($item);
            }
        }
    }    
    
    /**
     * Sets the parser plugin collection
     * 
     * @param PHPOnix_Domain_ParserCollectionInterface $collection
     * @return PHPOnix_ProductObserver
     */
    public function setParserCollection(PHPOnix_Domain_ParserCollectionInterface $collection)
    {
        $this->_parserCollection = $collection;
        return $this;
    }
    
    /**
     * Sets the importer plugin collection
     * 
     * @param PHPOnix_Domain_ImporterCollectionInterface $collection
     * @return PHPOnix_ProductObserver
     */
    public function setImporterCollection(PHPOnix_Domain_ImporterCollectionInterface $collection)
    {
        $this->_importerCollection = $collection;
        return $this;        
    }
    
    /**
     * It's triggered by the Subject object when it finds an end of Tag
     * Parses the Tag Product via parser plugins
     * Import the parsed values via importer plugins
     * 
     * @param SplSubject $subject
     */
    public function update(SplSubject $subject) 
    {
        
        if ('Product' === $subject->getCurrentTagName()) {

            $item = $this->_parse(new SimpleXMLElement($subject->getCurrentContent()));
            
            $this->_import($item);
            
            echo 'called Product Observer' . '<br />';
            echo '<pre>';
            echo print_r($item, true);
            echo '</pre>';
 
        }
    }    
}
