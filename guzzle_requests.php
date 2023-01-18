<?php
# scraping books to scrape: first Martinus.sk
#load the composer autoloader
require 'vendor/autoload.php';

//Initialize an array for results
$book_result = array();
$httpClient = new \GuzzleHttp\Client();


$book = 'harry';
#first response with a filter for a book
$response = $httpClient->get('https://www.martinus.sk/search?q='.$book.'r&types%5B0%5D=kniha');
$htmlString = (string) $response->getBody();
//add this line to suppress any warnings
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($htmlString);
$xpath = new DOMXPath($doc);
#Create a new array for results
$result = [];
#select from results only the titles and prices
$titles = $xpath->evaluate('//div[@class="listing pt-small pt-m-medium"]//div[@class="listing__item"]//div[@class="listing__item__details flex-1"]/a');
$price = $xpath->evaluate('//div[@class="listing pt-small pt-m-medium"]//div[@class="listing__item"]//div[@class="format__price"]');

foreach ($titles as $key => $title) {
    #Get price
    $price_clean = $price[$key]->textContent;
    #select only first 3 characters aftrer coma
    $price_clean = substr($price_clean, strpos($price_clean, ",") -3);  
    #remove €
    $price_clean = strtok($price[$key]->textContent, '€');
    //remove spaces
    $price_clean = str_replace(' ', '', $price_clean);
    $price_clean = str_replace(array("\n", "\r"), '', $price_clean);

    #create a new object and insert data
    if ($price_clean == '') {
        $price_clean = '999,99';
    }
    $record = new stdClass();
    $record->title = $title->textContent;
    $record->price = $price_clean;
    $record->shop = 'Martinus.sk';
    $book_result[] = $record;
  
}

#just continue to another shop

$response = $httpClient->get('https://www.pantarhei.sk/catalogsearch/result/?q='.$book.'&lb.f%5B%5D=category_path%3AKnihy');
$htmlString = (string) $response->getBody();
//add this line to suppress any warnings
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($htmlString);
$xpath = new DOMXPath($doc);
//Create a new array for results
$result = [];
//select from results only the titles and prices
$titles = $xpath->evaluate('//div[@class="row no-gutters product-list products wrapper"]//h2/a');
$price = $xpath->evaluate('//div[@class="row no-gutters product-list products wrapper"]//div[@class="row actions align-items-baseline mt-n3"]//span[@class="price-wrapper "]');
//Initialize an array.

foreach ($titles as $key => $title) {
    // Get price
    $price_clean = $price[$key]->textContent;
    // remove €
    $price_clean = strtok($price[$key]->textContent, '€');
    // remove spaces
    $price_clean = str_replace(' ', '', $price_clean);
    // remove empty from title
    $title = ltrim($title->textContent);
    // create a new object and insert data
    $record = new stdClass();
    $record->title = $title;
    $record->price = $price_clean;
    $record->shop = 'Pantarhei.sk';
    $book_result[] = $record;
  
}

//function to sort prices in array of objects from lowest to highest
usort($book_result, fn($a, $b) => strcmp($a->price, $b->price));

//array object to array 

$array = json_decode(json_encode($book_result), true);

//to convert array 
function html_table($data = array())
{
    $rows = array();
    foreach ($data as $row) {
        $cells = array();
        foreach ($row as $cell) {
            $cells[] = " ".$cell;
        }
        $rows[] = "" . implode('', $cells) . "\n";
    }
    return  implode('', $rows) ;
}
//result
echo html_table($array);