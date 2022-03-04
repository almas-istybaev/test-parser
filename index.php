<?php

/**
 * Парсим данные в массив и сохраняем в файл
 */
class ParserToJson
{
    public string $baseUrl = 'https://site.name';

    private array $data = [];

    private function getNodes(string $link)
    {
        $searchPage = file_get_contents($link);
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);

        $dom->loadHTML($searchPage);
        libxml_use_internal_errors($internalErrors);

        $tables = $dom->getElementsByTagName('tbody');

        return $tables[0]->childNodes;
    }

    private function parseNode($node): void
    {
        if($node->childNodes->length > 3) {
            $item['img'] = trim($node->childNodes[3]->childNodes[1]->attributes->getNamedItem("src")->nodeValue);
            $item['name'] = trim($node->childNodes[11]->textContent);
            $item['brand'] = trim($node->childNodes[7]->textContent);
            $item['article'] = trim($node->childNodes[9]->textContent);
            $item['count'] = trim($node->childNodes[15]->textContent);
            $item['time'] = trim($node->childNodes[19]->textContent);
            $item['price'] = trim($node->childNodes[25]->textContent);
            $item['id'] = trim($node->childNodes[31]->lastChild->previousSibling->attributes->getNamedItem("searchresultuniqueid")->nodeValue);
            $this->data[] = $item;
        }
    }

    private function parse (string $link) : void
    {
        foreach ($this->getNodes($link) as $node) {
            if($node->childNodes->length > 3 && $node->hasAttributes() && $node->attributes->getNamedItem("data-link")) {
                foreach ($this->getNodes($this->baseUrl . $node->attributes->getNamedItem("data-link")->nodeValue) as $mainNode) {
                    $this->parseNode($mainNode);
                }
                break;
            }
            $this->parseNode($node);
        }
    }

    private function writeToFile (): void
    {
        try {
            file_put_contents('search_result.txt', json_encode($this->data));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function search (string $text): array
    {
        $text = htmlspecialchars($text);
        $this->parse($this->baseUrl . '/search/?pcode=' . $text);
        $this->writeToFile();

        return $this->data;
    }
}

$parser = new ParserToJson();
$data = $parser->search('search-article');
print_r($data);
