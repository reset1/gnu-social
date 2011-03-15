<?php
/*
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * Class for outputting a widget to display or edit
 * extended profiles
 */
class ExtendedProfileWidget extends Form
{
    const EDITABLE = true;

    /**
     * The parent profile
     *
     * @var Profile
     */
    protected $profile;

    /**
     * The extended profile
     *
     * @var Extended_profile
     */
    protected $ext;

    /**
     * Constructor
     *
     * @param XMLOutputter  $out
     * @param Profile       $profile
     * @param boolean       $editable
     */
    public function __construct(XMLOutputter $out=null, Profile $profile=null, $editable=false)
    {
        parent::__construct($out);

        $this->profile = $profile;
        $this->ext = new ExtendedProfile($this->profile);

        $this->editable = $editable;
    }

    /**
     * Show the extended profile, or the edit form
     */
    public function show()
    {
        if ($this->editable) {
            parent::show();
        } else {
            $this->showSections();
        }
    }

    /**
     * Show form data
     */
    public function formData()
    {
        $this->showSections();
    }

    /**
     * Show each section of the extended profile
     */
    public function showSections()
    {
        $sections = $this->ext->getSections();
        foreach ($sections as $name => $section) {
            $this->showExtendedProfileSection($name, $section);
        }
    }

    /**
     * Show an extended profile section
     *
     * @param string $name      name of the section
     * @param array  $section   array of fields for the section
     */
    protected function showExtendedProfileSection($name, $section)
    {
        $this->out->element('h3', null, $section['label']);
        $this->out->elementStart('table', array('class' => 'extended-profile'));

        foreach ($section['fields'] as $fieldName => $field) {

            switch($fieldName) {
            case 'phone':
            case 'experience':
                $this->showMultiple($fieldName, $field);
                break;
            default:
                $this->showExtendedProfileField($fieldName, $field);
            }
        }
        $this->out->elementEnd('table');
    }

    /**
     * Show an extended profile field
     *
     * @param string $name  name of the field
     * @param array  $field set of key/value pairs for the field
     */
    protected function showExtendedProfileField($name, $field)
    {
        $this->out->elementStart('tr');

        $this->out->element('th', null, $field['label']);

        $this->out->elementStart('td');
        if ($this->editable) {
            $this->showEditableField($name, $field);
        } else {
            $this->showFieldValue($name, $field);
        }
        $this->out->elementEnd('td');

        $this->out->elementEnd('tr');
    }

    protected function showMultiple($name, $fields) {
        foreach ($fields as $field) {
            $this->showExtendedProfileField($name, $field);
        }
    }

    protected function showPhone($name, $field)
    {
        $this->out->elementStart('div', array('class' => 'phone-display'));
        $this->out->text($field['value']);
        if (!empty($field['rel'])) {
            $this->out->text(' (' . $field['rel'] . ')');
        }
        $this->out->elementEnd('div');
    }

    protected function showEditablePhone($name, $field)
    {
        $index = isset($field['index']) ? $field['index'] : 0;
        $id    = "extprofile-$name-$index";
        $rel   = $id . '-rel';
        $this->out->elementStart(
            'div', array(
                'id' => $id . '-edit',
                'class' => 'phone-edit'
            )
        );
        $this->out->input(
            $id,
            null,
            isset($field['value']) ? $field['value'] : null
        );
        $this->out->dropdown(
            $id . '-rel',
            'Type',
            array(
                'office' => 'Office',
                'mobile' => 'Mobile',
                'home'   => 'Home',
                'pager'  => 'Pager',
                'other'  => 'Other'
            ),
            null,
            false,
            isset($field['rel']) ? $field['rel'] : null
        );

        $this->showMultiControls();
        $this->out->elementEnd('div');
    }

    protected function showExperience($name, $field)
    {
        $this->out->elementStart('div', 'experience-item');
        $this->out->element('div', 'field', $field['company']);
        $this->out->element('div', 'label', _m('Start'));
        $this->out->element('div', array('class' => 'field date'), $field['start']);
        $this->out->element('div', 'label', _m('End'));
        $this->out->element('div', array('class' => 'field date'), $field['end']);
        if ($field['current']) {
            $this->out->element(
                'div',
                array('class' => 'field current'),
                '(' . _m('Current') . ')'
            );
        }
        $this->out->elementEnd('div');
    }

    protected function showEditableExperience($name, $field)
    {
        $index = isset($field['index']) ? $field['index'] : 0;
        $id    = "extprofile-$name-$index";
        $this->out->elementStart(
            'div', array(
                'id' => $id . '-edit',
                'class' => 'experience-edit'
            )
        );

        $this->out->input(
            $id,
            null,
            isset($field['company']) ? $field['company'] : null
        );

        $this->out->elementStart('ul', 'experience-start-and-end');
        $this->out->elementStart('li');
        $this->out->input(
            $id . '-start',
            _m('Start'),
            isset($field['start']) ? $field['start'] : null
        );
        $this->out->elementEnd('li');

        $this->out->elementStart('li');
        $this->out->input(
            $id . '-end',
            _m('End'),
            isset($field['end']) ? $field['end'] : null
        );
        $this->out->elementEnd('li');
        $this->out->elementStart('li');
        $this->out->hidden(
            $id . '-current',
            'false'
        );
        $this->out->checkbox(
            $id . '-current',
            _m('Current'),
            $field['current']
        );
        $this->out->elementEnd('li');
        $this->out->elementEnd('ul');
        $this->showMultiControls();
        $this->out->elementEnd('div');
    }

    function showMultiControls()
    {
        $this->out->element(
            'a',
            array(
                'class' => 'add_row',
                'href' => 'javascript://',
                'style' => 'display: none; '
            ),
            '+'
        );

        $this->out->element(
            'a',
            array(
                'class' => 'remove_row',
                'href' => 'javascript://',
                'style' => 'display: none; '
            ),
            '-'
        );
    }

    /**
     * Outputs the value of a field
     *
     * @param string $name  name of the field
     * @param array  $field set of key/value pairs for the field
     */
    protected function showFieldValue($name, $field)
    {
        $type = strval(@$field['type']);

        switch($type)
        {
        case '':
        case 'text':
        case 'textarea':
            $this->out->text($this->ext->getTextValue($name));
            break;
        case 'tags':
            $this->out->text($this->ext->getTags());
            break;
        case 'phone':
            $this->showPhone($name, $field);
            break;
        case 'experience':
            $this->showExperience($name, $field);
            break;
        default:
            $this->out->text("TYPE: $type");
        }
    }

    /**
     * Show an editable version of the field
     *
     * @param string $name  name fo the field
     * @param array  $field array of key/value pairs for the field
     */
    protected function showEditableField($name, $field)
    {
        $out = $this->out;

        $type = strval(@$field['type']);
        $id = "extprofile-" . $name;

        $value = 'placeholder';

        switch ($type) {
        case '':
        case 'text':
            $out->input($id, null, $this->ext->getTextValue($name));
            break;
        case 'textarea':
            $out->textarea($id, null,  $this->ext->getTextValue($name));
            break;
        case 'tags':
            $out->input($id, null, $this->ext->getTags());
            break;
        case 'phone':
            $this->showEditablePhone($name, $field);
            break;
        case 'experience':
            $this->showEditableExperience($name, $field);
            break;
        default:
            $out->input($id, null, "TYPE: $type");
        }
    }

    /**
     * Action elements
     *
     * @return void
     */

    function formActions()
    {
        $this->out->submit(
            'save',
            _m('BUTTON','Save'),
            'submit form_action-secondary',
            'save',
            _('Save details')
       );
    }

    /**
     * ID of the form
     *
     * @return string ID of the form
     */

    function id()
    {
        return 'profile-details-' . $this->profile->id;
    }

    /**
     * class of the form
     *
     * @return string of the form class
     */

    function formClass()
    {
        return 'form_profile_details';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */

    function action()
    {
        return common_local_url('profiledetailsettings');
    }
}
