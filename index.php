<?php
define('PHPONIX_PATH', '/var/www/');
function syringeAutoloader($className) {
    $className = str_replace('_', '/', $className);
    include PHPONIX_PATH . $className . '.php';
}
spl_autoload_register('syringeAutoloader');

$parsers = new PHPOnix_Domain_ParserCollection(
    array(
        'authors' => new PHPOnix_Parser_Authors(),
        'publishers' => new PHPOnix_Parser_Publishers(),
        'headers' => new PHPOnix_Parser_BookHeaders(),
        'prices' => new PHPOnix_Parser_Prices(),
        'images' => new PHPOnix_Parser_Images(),
        'categories' => new PHPOnix_Parser_Categories(),            
    )
);
$importers = new PHPOnix_Domain_ImporterCollection(
    array(
        'db' => new PHPOnix_Importer_Sql(),        
    )
);
$productObserver = new PHPOnix_ProductObserver();
$productObserver->setParserCollection($parsers)
                ->setImporterCollection($importers);

$parserSubject = new PHPOnix_ParserSubject();
$parserSubject->attach($productObserver)
              ->attach(new PHPOnix_HeaderObserver);
              
$reader = new PHPOnix_CantbookReader;
$parserSubject->parse($reader->read());

