<?php

namespace Core\Component\HtmlBlock {
    use \DOMDocument as DOMDocument;
    use Core\Exception\WException;

    class HtmlBlock {
        private $dom_document;
        private $html_node_document;
        private $lang = '';
        private $encoding = 'UTF-8';

        public function __construct($encoding = null) {
            if (empty($encoding)) {
                $encoding = $this->encoding;
            }

            $dom_document = new DOMDocument(null,$encoding);

            $this->setDomDocument($dom_document);

            $this->createHtmlElement();

            return $this;
        }

        public function getDomDocument() {
            return $this->dom_document;
        }

        public function setDomDocument($dom_document) {
            $this->dom_document = $dom_document;
        }

        public function getHtmlNodeDocument() {
            return $this->html_node_document;
        }

        public function setHtmlNodeDocument($html_node_document) {
            $this->html_node_document = $html_node_document;
        }

        public function renderHtml() {
            $dom_document = $this->getDomDocument();

            return $dom_document->saveHTML();
        }

        public function createHtmlElement() {
            $html_element = $this->dom_document->createElement('html');
            $html_node_document = $this->dom_document->appendChild($html_element);

            $this->setHtmlNodeDocument($html_node_document);
            
            return $this;
        }

        public function appendElement($element) {
            $html_node_document = $this->getHtmlNodeDocument();

            $html_node_document->appendChild($element);

            return $this;
        }

        public function createElement($name,$content = null) {
            $element = $this->dom_document->createElement($name,$content);

            return $element;
        }
    }
}
