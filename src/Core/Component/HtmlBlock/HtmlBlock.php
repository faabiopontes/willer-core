<?php

namespace Core\Component\HtmlBlock {
    use \DOMDocument as DOMDocument;
    use Core\Exception\WException;

    class HtmlBlock extends DOMDocument {
        private $dom_document;
        private $lang = '';
        private $encoding = 'UTF-8';

        public function __construct($encoding = null) {
            if (empty($encoding)) {
                $encoding = $this->encoding;
            }

            $this->dom_document = new DOMDocument(null,$encoding);

            return $this;
        }

        public function createElement($name,$content = null) {
            return $this->dom_document->createElement($name,$content);
        }

        public function createAttribute($element,$name,$value = null) {
            $element_create_attribute = $element->createAttribute($name);

            if (!empty($value)) {
                $element_create_attribute->value = $value;
            }

            return $element;
        }
    }
}
