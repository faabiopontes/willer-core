<?php

namespace Core\Component\HtmlBlock {
    use Core\Exception\WException;
    use Core\Util;

    class Form {
        private $html_block;
        private $form_element;
        private $model;
        private $label;
        private $title;
        private $text;
        private $footer;
        private $node_div_panel_head;
        private $node_div_panel_body;
        private $node_div_panel_footer;

        public function __construct($html_block,...$kwargs) {
            $this->setHtmlBlock($html_block);

            if (!empty($kwargs)) {
                $kwargs = $kwargs[0];
            }

            $model = Util::get($kwargs,'model',null);
            $this->setModel($model);

            $label = Util::get($kwargs,'label',null);
            $this->setLabel($label);

            $title = Util::get($kwargs,'title',null);
            $this->setTitle($title);

            $text = Util::get($kwargs,'text',null);
            $this->setText($text);

            $footer = Util::get($kwargs,'footer',null);
            $this->setFooter($footer);

            $form_element = $html_block->createElement('form');

            if (isset($kwargs['id']) && !empty($kwargs['id'])) {
                $form_element->setAttribute('id',$kwargs['id']);
            }

            if (isset($kwargs['class']) && !empty($kwargs['class'])) {
                $form_element->setAttribute('class',$kwargs['class']);
            }

            if (isset($kwargs['style']) && !empty($kwargs['style'])) {
                $form_element->setAttribute('style',$kwargs['style']);
            }

            if (isset($kwargs['name']) && !empty($kwargs['name'])) {
                $form_element->setAttribute('name',$kwargs['name']);
            }

            if (isset($kwargs['method']) && !empty($kwargs['method'])) {
                $form_element->setAttribute('method',$kwargs['method']);
            }

            if (isset($kwargs['action']) && !empty($kwargs['action'])) {
                $form_element->setAttribute('action',$kwargs['action']);
            }

            if (isset($kwargs['enctype']) && !empty($kwargs['enctype'])) {
                $form_element->setAttribute('enctype',$kwargs['enctype']);
            }

            if (isset($kwargs['novalidate']) && !empty($kwargs['novalidate'])) {
                $form_element->setAttribute('novalidate',$kwargs['novalidate']);
            }

            if (isset($kwargs['target']) && !empty($kwargs['target'])) {
                $form_element->setAttribute('target',$kwargs['target']);
            }

            $this->setDomElement($form_element);

            $this->readyModel();

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

        private function getLabel() {
            return $this->label;
        }

        private function setLabel($label) {
            $this->label = $label;
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

        public function getDomElement() {
            return $this->form_element;
        }

        private function setDomElement($form_element) {
            $this->form_element = $form_element;
        }

        private function getNodeDivPanelHead() {
            return $this->node_div_panel_head;
        }

        private function setNodeDivPanelHead($node_div_panel_head) {
            $this->node_div_panel_head = $node_div_panel_head;
        }

        private function getNodeDivPanelBody() {
            return $this->node_div_panel_body;
        }

        private function setNodeDivPanelBody($node_div_panel_body) {
            $this->node_div_panel_body = $node_div_panel_body;
        }

        private function getNodeDivPanelFooter() {
            return $this->node_div_panel_footer;
        }

        private function setNodeDivPanelFooter($node_div_panel_footer) {
            $this->node_div_panel_footer = $node_div_panel_footer;
        }

        private function addFieldForeignKey($field) {
            $html_block = $this->getHtmlBlock();

            $div = $html_block->createElement('div');
            $div->setAttribute('class','form-group');

            $label = $html_block->createElement('label',$field);
            $label->setAttribute('for','id_'.$field);

            $input = $html_block->createElement('select');
            $input->setAttribute('name',$field);
            $input->setAttribute('class','form-control');
            $input->setAttribute('id','id_'.$field);

            $div->appendChild($label);
            $div->appendChild($input);

            return $div;
        }

        private function addFieldChar($field) {
            $html_block = $this->getHtmlBlock();

            $div = $html_block->createElement('div');
            $div->setAttribute('class','form-group');

            $label = $html_block->createElement('label',$field);
            $label->setAttribute('for','id_'.$field);

            $input = $html_block->createElement('input');
            $input->setAttribute('name',$field);
            $input->setAttribute('type','text');
            $input->setAttribute('class','form-control');
            $input->setAttribute('id','id_'.$field);

            $div->appendChild($label);
            $div->appendChild($input);

            return $div;
        }

        private function addFieldBoolean($field) {
            $html_block = $this->getHtmlBlock();

            $div = $html_block->createElement('div');
            $div->setAttribute('class','checkbox');

            $label = $html_block->createElement('label');

            $paragraph = $html_block->createElement('p',$field);

            $input = $html_block->createElement('input');
            $input->setAttribute('name',$field);
            $input->setAttribute('value','1');
            $input->setAttribute('type','checkbox');
            $input->setAttribute('id','id_'.$field);

            $label->appendChild($input);
            $label->appendChild($paragraph);
            $div->appendChild($label);

            return $div;
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
                $this->setNodeDivPanelHead($node_div_panel_head);
            }

            if (!empty($text)) {
                $div_class_panel_body = $html_block->createElement('div',$text);
                $div_class_panel_body->setAttribute('class','panel-body');

                $node_div_panel_body = $div_class_panel->appendChild($div_class_panel_body);
                $this->setNodeDivPanelBody($node_div_panel_body);
            }

            $div_container = $html_block->createElement('div');
            $div_container->setAttribute('class','container-fluid');
            $div_container->setAttribute('style','padding: 5px 0px;');

            $div_container->appendChild($dom_element);

            $div_class_panel->appendChild($div_container);

            if (!empty($footer)) {
                $div_class_panel_footer = $html_block->createElement('div',$footer);
                $div_class_panel_footer->setAttribute('class','panel-footer');

                $node_div_panel_footer = $div_class_panel->appendChild($div_class_panel_footer);
                $this->setNodeDivPanelFooter($node_div_panel_footer);
            }

            $this->setDomElement($div_class_panel);
        }

        private function readyModel() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();
            $model = $this->getModel();

            foreach ($model->schema() as $field => $schema) {
                if ($schema->method == 'foreignKey') {
                    $add_field_foreignkey = $this->addFieldForeignKey($field);

                    $dom_element->appendChild($add_field_foreignkey);

                } else if ($schema->method == 'char') {
                    $add_field_char = $this->addFieldChar($field);

                    $dom_element->appendChild($add_field_char);

                } else if ($schema->method == 'boolean') {
                    $add_field_boolean = $this->addFieldBoolean($field);

                    $dom_element->appendChild($add_field_boolean);
                }
            }

            $button = $html_block->createElement('button','Salvar');
            $button->setAttribute('type','submit');
            $button->setAttribute('class','btn btn-default');

            $dom_element->appendChild($button);

            $this->addPanel();
        }

        public function renderHtml() {
            $html_block = $this->getHtmlBlock();

            $html_block->appendBodyContainerRow($this);

            return $html_block->renderHtml();
        }
    }
}
