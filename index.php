<?php

/**
 * Парсим данные в массив и сохраняем в файл
 */
class ParserToJson
{
    
}

$parser = new ParserToJson();
$data = $parser->search('90915-YZZE2');
print_r($data);
