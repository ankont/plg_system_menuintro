<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.menuintro
 * @copyright   (C) 2025 Kontarinis Andreas — with help from ChatGPT
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

require_once __DIR__ . '/src/Renderer.php';

class PlgSystemMenuintro extends CMSPlugin
{
    /**
     * Ensure plugin language files are loaded for both site & admin.
     */
    protected function loadPluginLanguage(): void
    {
        // Standard load
        $this->loadLanguage();
        // Fallback explicit paths
        $lang = \Joomla\CMS\Factory::getLanguage();
        $lang->load('plg_system_menuintro', __DIR__);
    }

    /**
     * Add extra fields into com_menus.item form (stored in params)
     */
    public function onContentPrepareForm($form, $data)
    {
        // 1) Τρέξε ΜΟΝΟ για τη φόρμα menu item
        if (!($form instanceof Form) || $form->getName() !== 'com_menus.item') {
            return;
        }

        // 2) Φόρτωσε γλώσσες (για να φαίνονται τα labels)
        $this->loadPluginLanguage();

        // 3) Δήλωσε το path και φόρτωσε τη δική μας φόρμα
        Form::addFormPath(__DIR__ . '/forms');
        $form->loadFile('menuintro', false);
    }

    /**
     * Auto inject mode: insert the intro block before the component without touching template files.
     */
    public function onBeforeRender()
    {
        if ($this->params->get('render_mode', 'template') !== 'auto') return;

        $app = \Joomla\CMS\Factory::getApplication();
        if (!$app->isClient('site')) return;

        $doc = $app->getDocument();
        if ($doc->getType() !== 'html') return;

        $menu = $app->getMenu()->getActive();
        if (!$menu) return;

        $intro = \MenuIntro\Renderer::renderFromMenuParams($menu->getParams());
        if ($intro === '') return;

        $component = $doc->getBuffer('component');
        if ($component !== null) {
            $doc->setBuffer($intro . $component, 'component');
        }
    }

    /**
     * Helper to call from template in "template" render mode.
     * Use like this in template where you want it to appear:
     *   <?php
     *     \Joomla\CMS\Plugin\PluginHelper::importPlugin('system', 'menuintro');
     *     if (class_exists('PlgSystemMenuintro')) {
     *       PlgSystemMenuintro::renderActiveMenuIntro();
     *     }
     *   ?>
     */
    public static function renderActiveMenuIntro(): void
    {
        $app = Factory::getApplication();
        if (!$app->isClient('site')) {
            return;
        }
        $menu = $app->getMenu()->getActive();
        if (!$menu) {
            return;
        }
        $html = \MenuIntro\Renderer::renderFromMenuParams($menu->getParams());
        if ($html) {
            echo $html;
        }
    }
}
