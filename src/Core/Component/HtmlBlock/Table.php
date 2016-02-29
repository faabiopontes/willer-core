<?php

namespace Core\Component\HtmlBlock {
    use Core\Exception\WException;
    use Core\Util;

    class Table {
        private $html_block;
        private $table_element;

        public function __construct($html_block,...$kwargs) {
            $this->setHtmlBlock($html_block);

            if (!empty($kwargs)) {
                $kwargs = $kwargs[0];
            }

            $value = Util::get($kwargs,'value',null);

            $table_element = $html_block->createElement('table',$value);

            if (isset($kwargs['id']) && !empty($kwargs['id'])) {
                $table_element->setAttribute('id',$kwargs['id']);
            }

            if (isset($kwargs['class']) && !empty($kwargs['class'])) {
                $table_element->setAttribute('class',$kwargs['class']);
            }

            if (isset($kwargs['style']) && !empty($kwargs['style'])) {
                $table_element->setAttribute('style',$kwargs['style']);
            }

            $table_tbody_element = $html_block->createElement('tbody');

            $table_tbody_tr_element = $html_block->createElement('tr');

            $table_tbody_tr_td_element = $html_block->createElement('td','test');

            $table_tbody_tr_element->appendChild($table_tbody_tr_td_element);

            $table_tbody_element->appendChild($table_tbody_tr_element);

            $table_element->appendChild($table_tbody_element);

            $this->setDomElement($table_element);

            return $this;
        }

        public function getHtmlBlock() {
            return $this->html_block;
        }

        public function setHtmlBlock($html_block) {
            $this->html_block = $html_block;
        }

        public function getDomElement() {
            return $this->table_element;
        }

        public function setDomElement($table_element) {
            $this->table_element = $table_element;
        }

        public function renderHtml() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();

            $html_block->appendElement($dom_element);

            return $html_block->renderHtml();
        }
    }
}
