<?php

namespace Core\Component\HtmlBlock {
    use Core\Exception\WException;
    use Core\Util;

    class Form {
        private $html_block;
        private $dom_element;
        private $model;
        private $label;
        private $container_class;
        private $container_style;

        public function __construct($html_block,...$kwargs) {
            $this->setHtmlBlock($html_block);

            if (!empty($kwargs)) {
                $kwargs = $kwargs[0];
            }

            $model = Util::get($kwargs,'model',null);
            $this->setModel($model);

            $label = Util::get($kwargs,'label',null);
            $this->setLabel($label);

            $container_class = Util::get($kwargs,'container_class',null);
            $this->setContainerClass($container_class);

            $container_style = Util::get($kwargs,'container_style',null);
            $this->setContainerStyle($container_style);

            $dom_element = $html_block->createElement('form');

            if (isset($kwargs['id']) && !empty($kwargs['id'])) {
                $dom_element->setAttribute('id',$kwargs['id']);
            }

            if (isset($kwargs['class']) && !empty($kwargs['class'])) {
                $dom_element->setAttribute('class',$kwargs['class']);
            }

            if (isset($kwargs['style']) && !empty($kwargs['style'])) {
                $dom_element->setAttribute('style',$kwargs['style']);
            }

            if (isset($kwargs['name']) && !empty($kwargs['name'])) {
                $dom_element->setAttribute('name',$kwargs['name']);
            }

            if (isset($kwargs['method']) && !empty($kwargs['method'])) {
                $dom_element->setAttribute('method',$kwargs['method']);
            }

            if (isset($kwargs['action']) && !empty($kwargs['action'])) {
                $dom_element->setAttribute('action',$kwargs['action']);
            }

            if (isset($kwargs['enctype']) && !empty($kwargs['enctype'])) {
                $dom_element->setAttribute('enctype',$kwargs['enctype']);
            }

            if (isset($kwargs['novalidate']) && !empty($kwargs['novalidate'])) {
                $dom_element->setAttribute('novalidate',$kwargs['novalidate']);
            }

            if (isset($kwargs['target']) && !empty($kwargs['target'])) {
                $dom_element->setAttribute('target',$kwargs['target']);
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

        private function getLabel() {
            return $this->label;
        }

        private function setLabel($label) {
            $this->label = $label;
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

            return $div_class_col;
        }

        private function ready() {
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

            $add_container = $this->addContainer();

            $this->setDomElement($add_container);
        }

        public function renderHtml() {
            $html_block = $this->getHtmlBlock();

            $html_block->appendBodyContainerRow($this);

            return $html_block->renderHtml();
        }
    }
}
