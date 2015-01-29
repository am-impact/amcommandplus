<?php
/**
 * Additional commands for a&m command that shouldn't be in the core.
 *
 * @package   Am Command Plus
 * @author    Hubert Prein
 */
namespace Craft;

class AmCommandPlusPlugin extends BasePlugin
{
    public function getName()
    {
         return 'a&m command plus';
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getDeveloper()
    {
        return 'a&m impact';
    }

    public function getDeveloperUrl()
    {
        return 'http://www.am-impact.nl';
    }

    /**
     * Add commands to a&m command through this hook function.
     *
     * @return array
     */
    public function addCommands() {
        $commands = array(
            array(
                'name'    => Craft::t('Globals') . ': ' . Craft::t('Create field in "{globalSetName}"', array('globalSetName' => Craft::t('Number of views'))),
                'info'    => Craft::t('Quickly create a field in the global set and add it to the field layout.'),
                'call'    => 'createFieldAction',
                'service' => 'amCommandPlus_globals',
                'vars'    => array(
                    'globalSetName' => Craft::t('Number of views')
                )
            ),
            array(
                'name'    => Craft::t('Globals') . ': ' . Craft::t('Create field in "{globalSetName}"', array('globalSetName' => Craft::t('Entry IDs'))),
                'info'    => Craft::t('Quickly create a field in the global set and add it to the field layout.'),
                'call'    => 'createFieldAction',
                'service' => 'amCommandPlus_globals',
                'vars'    => array(
                    'globalSetName' => Craft::t('Entry IDs')
                )
            )
        );
        return $commands;
    }
}