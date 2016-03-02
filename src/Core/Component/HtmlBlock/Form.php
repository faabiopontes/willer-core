<?php

namespace Core\Component\HtmlBlock {
    use Core\Exception\WException;
    use Core\Util;

    class Form {
        private $html_block;
        private $form_element;
        private $model;
        private $label;

        public function __construct($html_block,...$kwargs) {
            $this->setHtmlBlock($html_block);

            if (!empty($kwargs)) {
                $kwargs = $kwargs[0];
            }

            $model = Util::get($kwargs,'model',null);
            $this->setModel($model);

            $label = Util::get($kwargs,'label',null);
            $this->setLabel($label);

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

        public function getDomElement() {
            return $this->form_element;
        }

        private function setDomElement($form_element) {
            $this->form_element = $form_element;
        }

        private function readyModel() {
            $html_block = $this->getHtmlBlock();
            $dom_element = $this->getDomElement();
            $model = $this->getModel();

            foreach ($model->schema() as $field => $schema) {
                if ($schema->method == 'foreignKey') {
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

                    $dom_element->appendChild($div);

                } else if ($schema->method == 'char') {
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

                    $dom_element->appendChild($div);

                } else if ($schema->method == 'boolean') {
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

                    $dom_element->appendChild($div);
                }
            }

            $button = $html_block->createElement('button','Salvar');
            $button->setAttribute('type','submit');
            $button->setAttribute('class','btn btn-default');

            $dom_element->appendChild($button);

            $html_block->appendBody($this);
        }

        public function renderHtml() {
            $html_block = $this->getHtmlBlock();

            $html_block->appendBody($this);

            return $html_block->renderHtml();
        }
    }
}
