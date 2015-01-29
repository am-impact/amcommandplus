<?php
namespace Craft;

class AmCommandPlus_GlobalsService extends BaseApplicationComponent
{
    private $_globalSet;

    /**
     * Get the create a field action.
     *
     * @param array $variables
     *
     * @return bool
     */
    public function createFieldAction($variables)
    {
        if (! isset($variables['globalSetName'])) {
            return false;
        }
        craft()->amCommand->setReturnAction(Craft::t('New Field') . ':', '', 'createField', 'amCommandPlus_globals', $variables);
        return true;
    }

    /**
     * Create a field for a specific Global Set and it to the field layout.
     *
     * @param array $variables
     *
     * @return bool
     */
    public function createField($variables)
    {
        if (! isset($variables['globalSetName']) || ! isset($variables['searchText'])) {
            return false;
        }
        elseif (empty($variables['searchText']) || trim($variables['searchText']) == '') {
            craft()->amCommand->setReturnMessage(Craft::t('Field name isn’t set.'));
            return false;
        }

        $setName = $variables['globalSetName'];

        // Create a Number field
        $settings = array(
            'min' => '0'
        );
        $createdField = craft()->amCommandPlus_fields->createFieldInGroup($setName, $variables['searchText'], 'Number', $settings);
        if ($createdField === false) {
            craft()->amCommand->setReturnMessage(Craft::t('Couldn’t save field.'));
            return false;
        }

        // Create a layout field from the created field
        $layoutField = new FieldLayoutFieldModel();
        $layoutField->fieldId = $createdField->id;
        $layoutField->required = false;
        $layoutField->sortOrder = 1;

        // Get Global Set
        $this->_globalSet = $this->_getSetByName($setName);
        $this->_addFieldsToLayout(array($layoutField));

        craft()->globals->saveSet($this->_globalSet);

        // Return the result!
        craft()->amCommand->setReturnUrl($this->_globalSet->getCpEditUrl());
        craft()->amCommand->setReturnMessage(Craft::t('Field saved.'));
        return true;
    }

    /**
     * Get a Global Set by name.
     *
     * @param string $name
     *
     * @return GlobalSetModel
     */
    private function _getSetByName($name)
    {
        // Create handle based on the given name
        $handle = craft()->amCommandPlus->camelString($name);

        // Search for the Global Set
        $globalSet = craft()->globals->getSetByHandle($handle);

        // Create new set if we don't have one
        if (! $globalSet) {
            $globalSet = new GlobalSetModel();
            $globalSet->name = $name;
            $globalSet->handle = $handle;
        }

        return $globalSet;
    }

    /**
     * Add fields to Set's layout.
     *
     * @param array $fields Array filled with FieldLayoutFieldModel.
     */
    private function _addFieldsToLayout($fields)
    {
        $oldLayout = false;
        $newLayout = $this->_globalSet->getFieldLayout();

        $layoutTabs = array();
        $layoutFields = array();

        // Delete old layout if available
        if ($this->_globalSet->fieldLayoutId) {
            $oldLayout = $this->_globalSet->getFieldLayout();

            // Overwrite variables
            $layoutTabs = $oldLayout->getTabs();
            $layoutFields = $oldLayout->getFields();
        }

        // Create layout
        if (! count($layoutTabs)) {
            // Nothing is set, so we have to create it
            $tab = new FieldLayoutTabModel();
            $tab->name      = Craft::t('Content');
            $tab->sortOrder = 1;
            $tab->setFields($fields);

            $layoutTabs[] = $tab;

            // Add fields to layout
            foreach ($fields as $field) {
                $layoutFields[] = $field;
            }
        }
        else {
            // Update existing information
            foreach ($layoutTabs as $tab) {
                // Add fields to first tab
                if ($tab === reset($layoutTabs)) {
                    $tabFields = $tab->getFields();

                    $sortOrder = count($tabFields) + 1;
                    foreach ($fields as $field) {
                        // Update position for added field
                        $field->sortOrder = $sortOrder;

                        // Add field to tab and layout
                        $tabFields[] = $field;
                        $layoutFields[] = $field;

                        // Update order
                        $sortOrder ++;
                    }

                    $tab->setFields($tabFields);
                }
            }
        }
        $newLayout->setTabs($layoutTabs);
        $newLayout->setFields($layoutFields);

        // Delete old layout
        if ($oldLayout !== false) {
            craft()->fields->deleteLayoutById($oldLayout->id);
        }

        // Set new layout
        $this->_globalSet->setFieldLayout($newLayout);
    }
}