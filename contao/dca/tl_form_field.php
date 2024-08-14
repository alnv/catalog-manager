<?php

use Alnv\CatalogManagerBundle\Classes\tl_form_field;

$GLOBALS['TL_DCA']['tl_form_field']['config']['onload_callback'][] = [tl_form_field::class, 'setInfo'];