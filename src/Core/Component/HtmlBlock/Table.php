<?php
 
namespace Core\Component\HtmlBlock {
    use Core\Exception\WException;
    use Core\Util;
 
    class Table {
        private $html_block;
        private $dom_element;
        private $model;
        private $title;
        private $text;
        private $footer;
        private $node_table_thead;
        private $node_table_tbody;
        private $node_table_tfoot;
        private $container_class;
        private $container_style;
 
        public function __construct($html_block,...$kwargs) {
            $this->setHtmlBlock($html_block);
 
            if (!empty($kwargs)) {
                $kwargs = $kwargs[0];
            }
 
            $value = Util::get($kwargs,'value',null);
            $dom_element = $html_block->createElement('table',$value);
 
            $model = Util::get($kwargs,'model',null);
            $this->setModel($model);

            $title = Util::get($kwargs,'title',null);
            $this->setTitle($title);

            $text = Util::get($kwargs,'text',null);
            $this->setText($text);

            $footer = Util::get($kwargs,'footer',null);
            $this->setFooter($footer);
 
            $container_class = Util::get($kwargs,'container_class',null);
            $this->setContainerClass($container_class);
 
            $container_style = Util::get($kwargs,'container_style',null);
            $this->setContainerStyle($container_style);
 
            if (isset($kwargs['id']) && !empty($kwargs['id'])) {
                $dom_element->setAttribute('id',$kwargs['id']);
            }
 
            if (isset($kwargs['class']) && !empty($kwargs['class'])) {
                $dom_element->setAttribute('class',$kwargs['class']);
 
            } else {
                $dom_element->setAttribute('class','table table-striped table-bordered table-hover table-condensed');
            }
 
            if (isset($kwargs['style']) && !empty($kwargs['style'])) {
                $dom_element->setAttribute('style',$kwargs['style']);
            }
 
            $this->setDomElement($dom_element);
            $this->ready();
 
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

        private function getTitle() {
            return $this->title;
        }

        private function setTitle($title) {
            $this->title = $title;
        }

        private function getText() {
            return $this->text;
        }

        private function setText($text) {
            $this->text = $text;
        }

        private function getFooter() {
            return $this->footer;
        }

        private function setFooter($footer) {
            $this->footer = $footer;
        }
 
        private function getContainerClass() {
            return $this->container_class;
        }
 
        private function setContainerClass($container_class) {
            $this->container_class = $container_class;
        }
 
        private function getContainerStyle() {
            return $this->container_style;
        }
 
        private function setContainerStyle($container_style) {
            $this->container_style = $container_style;
        }
 
        public function getDomElement() {
            return $this->dom_element;
        }
 
        private function setDomElement($dom_element) {
            $this->dom_element = $dom_element;
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
 
        private function addThead() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();
            $model = $this->getModel();
 
            $table_thead_element = $html_block->createElement('thead');
            $node_table_thead = $dom_element->appendChild($table_thead_element);
            $this->setNodeTableThead($node_table_thead);
 
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
 
        private function addTbody() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();
            $model = $this->getModel();
 
            $table_tbody_element = $html_block->createElement('tbody');
            $node_table_tbody = $dom_element->appendChild($table_tbody_element);
            $this->setNodeTableTbody($node_table_tbody);
 
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

        private function addPanel() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();
            $title = $this->getTitle();
            $text = $this->getText();
            $footer = $this->getFooter();
 
            if (empty($title) && empty($text) && empty($footer)) {
                return false;
            }
 
            $div_class_panel = $html_block->createElement('div');
            $div_class_panel->setAttribute('class','panel panel-default');
 
            if (!empty($title)) {
                $div_class_panel_head = $html_block->createElement('div',$title);
                $div_class_panel_head->setAttribute('class','panel-heading');
                $node_div_panel_head = $div_class_panel->appendChild($div_class_panel_head);
            }
 
            $div_class_panel_body = $html_block->createElement('div');

            if (!empty($text)) {
                $p_text = $html_block->createElement('p',$text);
                $div_class_panel_body->appendChild($p_text);
            }

            $div_class_panel_body->setAttribute('class','panel-body');
            $node_div_panel_body = $div_class_panel->appendChild($div_class_panel_body);
            $node_div_panel_body->appendChild($dom_element);
 
            if (!empty($footer)) {
                $div_class_panel_footer = $html_block->createElement('div',$footer);
                $div_class_panel_footer->setAttribute('class','panel-footer');
                $node_div_panel_footer = $div_class_panel->appendChild($div_class_panel_footer);
            }
 
            $this->setDomElement($div_class_panel);
        }
 
        private function addContainer() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();
            $container_class = $this->getContainerClass();
            $container_style = $this->getContainerStyle();
 
            if (empty($container_class)) {
                $container_class = 'col-md-12';
            }
 
            $div_class_col = $html_block->createElement('div');
            $div_class_col->setAttribute('class',$container_class);
            $div_class_col->setAttribute('style',$container_style);
 
            $div_class_col->appendChild($dom_element);
 
            $this->setDomElement($div_class_col);
        }
 
        private function ready() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();
 
            $table_tfoot_element = $html_block->createElement('tfoot');
            $node_table_tfoot = $dom_element->appendChild($table_tfoot_element);
            $this->setNodeTableTfoot($node_table_tfoot);
 
            $this->addThead();
            $this->addTbody();
            $this->addPanel();
            $this->addContainer();
        }
 
        public function renderHtml() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();
 
            $html_block->appendBodyContainerRow($dom_element);
 
            return $html_block->renderHtml();
        }
    }
}
