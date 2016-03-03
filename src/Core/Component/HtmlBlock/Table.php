<?php

namespace Core\Component\HtmlBlock {
    use Core\Exception\WException;
    use Core\Util;

    class Table {
        private $html_block;
        private $table_element;
        private $model;
        private $node_table_thead;
        private $node_table_tbody;
        private $node_table_tfoot;

        public function __construct($html_block,...$kwargs) {
            $this->setHtmlBlock($html_block);

            if (!empty($kwargs)) {
                $kwargs = $kwargs[0];
            }

            $value = Util::get($kwargs,'value',null);
            $table_element = $html_block->createElement('table',$value);

            $model = Util::get($kwargs,'model',null);
            $this->setModel($model);

            if (isset($kwargs['id']) && !empty($kwargs['id'])) {
                $table_element->setAttribute('id',$kwargs['id']);
            }

            if (isset($kwargs['class']) && !empty($kwargs['class'])) {
                $table_element->setAttribute('class',$kwargs['class']);
            }

            if (isset($kwargs['style']) && !empty($kwargs['style'])) {
                $table_element->setAttribute('style',$kwargs['style']);
            }

            $table_thead_element = $html_block->createElement('thead');
            $node_table_thead = $table_element->appendChild($table_thead_element);
            $this->setNodeTableThead($node_table_thead);

            $table_tbody_element = $html_block->createElement('tbody');
            $node_table_tbody = $table_element->appendChild($table_tbody_element);
            $this->setNodeTableTbody($node_table_tbody);

            $table_tfoot_element = $html_block->createElement('tfoot');
            $node_table_tfoot = $table_element->appendChild($table_tfoot_element);
            $this->setNodeTableTfoot($node_table_tfoot);

            $this->setDomElement($table_element);

            $this->readyModelThead();
            $this->readyModelTbody();

            return $this;
        }

        private function getHtmlBlock() {
            return $this->html_block;
        }

        private function setHtmlBlock($html_block) {
            $this->html_block = $html_block;
        }

        private function getModel() {
            return $this->model;
        }

        private function setModel($model) {
            $this->model = $model;
        }

        public function getDomElement() {
            return $this->table_element;
        }

        private function setDomElement($table_element) {
            $this->table_element = $table_element;
        }

        private function getNodeTableThead() {
            return $this->node_table_thead;
        }

        private function setNodeTableThead($node_table_thead) {
            $this->node_table_thead = $node_table_thead;
        }

        private function getNodeTableTbody() {
            return $this->node_table_tbody;
        }

        private function setNodeTableTbody($node_table_tbody) {
            $this->node_table_tbody = $node_table_tbody;
        }

        private function getNodeTableTfoot() {
            return $this->node_table_tfoot;
        }

        private function setNodeTableTfoot($node_table_tfoot) {
            $this->node_table_tfoot = $node_table_tfoot;
        }

        private function modelLoop($html_block,$table_tr_element,$field_name,$object,$type) {
            foreach ($object as $field => $value) {
                if (is_object($value)) {
                    $this->modelLoop($table_tr_element,$field,$value);

                } else {
                    if ($type == 'th') {
                        $value = vsprintf('%s.%s',[$field_name,$field]);
                    }

                    $table_tbody_tr_td_or_th_element = $html_block->createElement($type,$value);
                    $table_tr_element->appendChild($table_tbody_tr_td_or_th_element);
                }
            }
        }

        private function readyModelThead() {
            $html_block = $this->getHtmlBlock();
            $node_table_thead = $this->getNodeTableThead();
            $model = $this->getModel();

            if (empty($model) || !is_array($model) || !isset($model['data']) || empty($model['data'])) {
                return false;
            }

            $data = $model['data'][0];

            $table_thead_tr_element = $html_block->createElement('tr');

            foreach ($data as $field => $value) {
                if (is_object($value)) {
                    $this->modelLoop($html_block,$table_thead_tr_element,$field,$value,'th');

                } else {
                    $table_thead_tr_th_element = $html_block->createElement('th',$field);
                    $table_thead_tr_element->appendChild($table_thead_tr_th_element);
                }
            }

            $node_table_thead->appendChild($table_thead_tr_element);
        }

        private function readyModelTbody() {
            $html_block = $this->getHtmlBlock();
            $node_table_tbody = $this->getNodeTableTbody();
            $model = $this->getModel();

            if (empty($model) || !is_array($model) || !isset($model['data']) || empty($model['data'])) {
                return false;
            }

            foreach ($model['data'] as $data) {
                $table_tbody_tr_element = $html_block->createElement('tr');

                foreach ($data as $field => $value) {
                    if (is_object($value)) {
                        $this->modelLoop($html_block,$table_tbody_tr_element,$field,$value,'td');

                    } else {
                        $table_tbody_tr_td_element = $html_block->createElement('td',$value);
                        $table_tbody_tr_element->appendChild($table_tbody_tr_td_element);
                    }
                }

                $node_table_tbody->appendChild($table_tbody_tr_element);
            }
        }

        public function renderHtml() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();

            $html_block->appendBodyContainerRow($dom_element);

            return $html_block->renderHtml();
        }
    }
}
