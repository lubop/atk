<?php

namespace Sintattica\Atk\Attributes;

use Sintattica\Atk\Attributes\Attribute;
use Sintattica\Atk\Core\Config;
use Sintattica\Atk\Core\Tools;
use Sintattica\Atk\DataGrid\DataGrid;
use Sintattica\Atk\Db\Query;
use Sintattica\Atk\Ui\Page;

/**
 * The atkBoolAttribute class represents an attribute of a node
 * that can either be true or false.
 *
 * @author Ivo Jansch <ivo@achievo.org>
 */
class AutocompleteAttribute extends Attribute
{
    public $m_remote_url = null;


    /**
     * Returns a piece of html code that can be used in a form to edit this
     * attribute's value.
     *
     * @param array $record The record that holds the value for this attribute.
     * @param string $fieldprefix The fieldprefix to put in front of the name
     *                            of any html form element for this attribute.
     * @param string $mode The mode we're in ('add' or 'edit')
     *
     * @return string A piece of htmlcode for editing this attribute
     */
    public function edit($record, $fieldprefix, $mode)
    {
        $id = $this->getHtmlId($fieldprefix);

        $page = $this->m_ownerInstance->getPage();

        $page->register_loadscript('
            $("#'.$id.'").autocomplete({
                source: "'.$this->m_remote_url.'",
                minLength: 2,
                select: function( event, ui ) {
                    $("#'.$id.'").val(ui.item.value);
                }
            });
        ');

        return parent::edit($record, $fieldprefix, $mode);
    }

    public function setRemoteUrl($url)
    {
        $this->m_remote_url = $url;
        return $this;
    }


}
