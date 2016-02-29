<?php

namespace Core\Component\HtmlBlock {
    use \DOMDocument as DOMDocument;
    use Core\Exception\WException;
    use Core\Util;

    class HtmlBlock {
        private $dom_document;
        private $html_node_document;
        private $html_node_head;
        private $html_node_head_title;
        private $html_node_body;
        private $encoding;
        private $doc_type;

        public function __construct(...$kwargs) {
            if (!empty($kwargs)) {
                $kwargs = $kwargs[0];
            }

            $encoding = Util::get($kwargs,'encoding','UTF-8');
            $this->setEncoding($encoding);

            $doc_type = Util::get($kwargs,'doc_type','<!DOCTYPE html>');
            $this->setDocType($doc_type);

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

            return $this;
        }

        public function getHtmlNodeDocument() {
            return $this->html_node_document;
        }

        public function setHtmlNodeDocument($html_node_document) {
            $this->html_node_document = $html_node_document;

            return $this;
        }

        public function getEncoding() {
            return $this->encoding;
        }

        public function setEncoding($encoding) {
            $this->encoding = $encoding;

            return $this;
        }

        public function getDocType() {
            return $this->doc_type;
        }

        public function setDocType($doc_type) {
            $this->doc_type = $doc_type;

            return $this;
        }

        public function getHtmlNodeHead() {
            return $this->html_node_head;
        }

        public function setHtmlNodeHead($html_node_head) {
            $this->html_node_head = $html_node_head;

            return $this;
        }

        public function getHtmlNodeHeadTitle() {
            return $this->html_node_head_title;
        }

        public function setHtmlNodeHeadTitle($html_node_head_title) {
            $this->html_node_head_title = $html_node_head_title;

            return $this;
        }

        public function setHeadTitleContent($head_title_content) {
            $html_node_head_title = $this->getHtmlNodeHeadTitle();
            $html_node_head_title->textContent = $head_title_content;

            return $this;
        }

        public function getHtmlNodeBody() {
            return $this->html_node_body;
        }

        public function setHtmlNodeBody($html_node_body) {
            $this->html_node_body = $html_node_body;

            return $this;
        }

        public function addCss($url,$media = 'all') {
            $link_element = $this->createElement('link');
            $link_element->setAttribute('type','text/css');
            $link_element->setAttribute('href',$url);
            $link_element->setAttribute('media',$media);

            $html_node_head = $this->getHtmlNodeHead();
            $html_node_head->appendChild($link_element);

            return $this;
        }

        public function addJs($url) {
            $script_element = $this->createElement('script');
            $script_element->setAttribute('type','text/javascript');
            $script_element->setAttribute('src',$url);

            $html_node_head = $this->getHtmlNodeHead();
            $html_node_head->appendChild($script_element);

            return $this;
        }

        // public function addMetaTag($name,$content) {
        //     $element = $this->document->createElement( 'meta' );
        //     $element->setAttribute( 'name', $name );
        //     $element->setAttribute( 'content', $content );
        //     $this->metas[] = $element;
        // }

        public function createHtmlElement() {
            $dom_document = $this->getDomDocument();

            $html_element = $dom_document->createElement('html');
            $head_element = $dom_document->createElement('head');
            $title_element = $dom_document->createElement('title');

            $html_node_head_title = $head_element->appendChild($title_element);
            $this->setHtmlNodeHeadTitle($html_node_head_title);

            $html_node_head = $html_element->appendChild($head_element);
            $this->setHtmlNodeHead($html_node_head);

            $body_element = $dom_document->createElement('body');

            $html_node_body = $html_element->appendChild($body_element);
            $this->setHtmlNodeBody($html_node_body);

            $html_node_document = $dom_document->appendChild($html_element);
            $this->setHtmlNodeDocument($html_node_document);
            
            return $this;
        }

        public function createElement($name,$content = null) {
            $dom_document = $this->getDomDocument();

            $element = $dom_document->createElement($name,$content);

            return $element;
        }

        public function appendBodyElement($element) {
            $html_node_body = $this->getHtmlNodeBody();

            $html_node_body->appendChild($element);

            return $this;
        }

        public function renderHtml() {
            $dom_document = $this->getDomDocument();
            $doc_type = $this->getDocType();

            return $doc_type.$dom_document->saveHTML();
        }
    }
}
