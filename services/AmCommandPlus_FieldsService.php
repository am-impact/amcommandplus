<?php
namespace Craft;

class AmCommandPlus_FieldsService extends BaseApplicationComponent
{
    private $_currentFieldTypes = false;
    private $_currentFieldGroups = false;

    /**
     * Create a field.
     *
     * @param string $groupName
     * @param string $name
     * @param string $type
     * @param string $settings     [Optional]
     * @param string $instructions [Optional]
     * @param bool   $translatable [Optional]
     *
     * @return bool|FieldModel
     */
    public function createFieldInGroup($groupName, $name, $type, $settings = false, $instructions = false, $translatable = false)
    {
        // Set current known Craft information
        $this->_setCurrentInfo();

        // Set Field Model
        $newField = new FieldModel();
        $newField->groupId      = $this->_getFieldGroupIdByName($groupName);
        $newField->name         = $name;
        $newField->handle       = craft()->amCommandPlus->camelString($name);
        $newField->translatable = $translatable;
        $newField->type         = $type;
        if ($instructions && is_string($instructions)) {
            $newField->instructions = $instructions;
        }
        if ($settings && is_array($settings)) {
            $newField->settings = $settings;
        }

        // Only install fields of which the type actually exists
        if (! isset($this->_currentFieldTypes[ $type ])) {
            // Translations
            $vars = array(
                'fieldName' => $name,
                'fieldType' => $type
            );

            AmCommandPlusPlugin::log(Craft::t('Couldn’t create the `{fieldName}` field, because the type `{fieldType}` doesn’t exist.', $vars));
            return false;
        }

        if(craft()->fields->saveField($newField)) {
            return $newField;
        }
        return false;
    }

    /**
     * Set everything ready in order to create a field.
     */
    private function _setCurrentInfo()
    {
        // Find current Field Types?
        if ($this->_currentFieldTypes === false) {
            $this->_setCurrentFieldTypes();
        }
        // Find current Field Groups?
        if ($this->_currentFieldGroups === false) {
            $this->_setCurrentFieldGroups();
        }
    }

    /**
     * Get current installed field types.
     */
    private function _setCurrentFieldTypes()
    {
        // Set variable to an array
        $this->_currentFieldTypes = array();

        // Searh for Field Types
        $this->_currentFieldTypes = craft()->fields->getAllFieldTypes();
    }

    /**
     * Get current field groups.
     */
    private function _setCurrentFieldGroups()
    {
        // Set variable to an array
        $this->_currentFieldGroups = array();

        // Search for groups
        $fieldGroups = craft()->fields->getAllGroups();
        foreach ($fieldGroups as $fieldGroup) {
            $this->_currentFieldGroups[$fieldGroup->id] = $fieldGroup->name;
        }
    }

    /**
     * Get a field group ID.
     *
     * @param string $name Field group name.
     *
     * @return int
     */
    private function _getFieldGroupIdByName($name)
    {
        // Search for the Field Group
        $fieldGroupId = array_search($name, $this->_currentFieldGroups);

        // Create the Field Group if it isn't available
        if (! $fieldGroupId) {
            $group = new FieldGroupModel();
            $group->name = $name;
            if (craft()->fields->saveGroup($group)) {
                // Add to current field groups
                $this->_currentFieldGroups[$group->id] = $group->name;
            } else {
                AmCommandPlusPlugin::log(Craft::t('Could not save the `{groupName}` field group.', array('groupName' => $name)), LogLevel::Warning);
            }
            return $group->id;
        }
        return $fieldGroupId;
    }
}