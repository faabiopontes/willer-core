<?php

namespace Core\Component\HtmlBlock {
    use Core\Component\HtmlBlock\HtmlBlock;
    use Core\Exception\WException;

    class Table extends HtmlBlock {
        public function __construct($encoding = null,$id = null,$class = null,$style = null,$value = null) {
            parent::__construct();

            $table_element = $this->createElement('table',$value);

            if (!empty($id)) {
                $table_element = $this->createAttribute($table_element,'id',$id);
            }

            if (!empty($class)) {
                $table_element = $this->createAttribute($table_element,'class',$class);
            }

            if (!empty($style)) {
                $table_element = $this->createAttribute($table_element,'style',$style);
            }

            return $this;
        }
    }
}
