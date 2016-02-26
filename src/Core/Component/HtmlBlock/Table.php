<?php

namespace Core\Component\HtmlBlock {
    use Core\Exception\WException;

    class Table {
        private $table_node_element;

        public function __construct($html_block,$id = null,$class = null,$style = null,$value = null) {
            $dom_document = $html_block->getDomDocument();

            $table_element = $dom_document->createElement('table',$value);
            $table_node_element = $dom_document->appendChild($table_element);

            if (!empty($id)) {
                $table_node_element->setAttribute('id',$id);
            }

            if (!empty($class)) {
                $table_node_element->setAttribute('class',$class);
            }

            if (!empty($style)) {
                $table_node_element->setAttribute('style',$style);
            }

            $this->table_node_element = $table_element;            

            return $this;
        }

        public function getDomElement() {
            return $this->table_node_element;
        }

        public function renderHtml() {
            $dom_document = $this->getDomDocument();

            return $dom_document->saveHTML();
        }
    }
}
