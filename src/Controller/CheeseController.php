<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */


namespace LeniaLabs\Cheese\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class CheeseController extends AbstractActionController
{

    protected $sl = null;

    protected $config = null;


    public function __construct ($sl)
    {

        $this->sl = $sl;

        $cheese_module_class = dirname(__NAMESPACE__) . '\\Module';
        $cheese_module = new $cheese_module_class();
        $this->config = $cheese_module->getConfig();
    }


    public function installAssetsAction ()
    {
    	$root_directory = getcwd();

    	$public_directory = $root_directory . '/' . $this->config['cheese.project.web_folder_name'] . '/';

    	if (!file_exists($public_directory)) {
    		return new ViewModel(array('report' => '<span class="error">folder <b>' . $public_directory . '</b> does not exist</span>'));
    	}
    	if (!is_writable($public_directory)) {
    		return new ViewModel(array('report' => '<span class="error">can not write into folder <b>' . $public_directory . '</b></span>'));
    	}

    	$module_directory = $root_directory . '/module/';

    	$recursive_directory_iterator = new \RecursiveDirectoryIterator($module_directory);
    	$recursive_iterator_iterator = new \RecursiveIteratorIterator($recursive_directory_iterator);
    	$pattern = '/^.*\\' . DIRECTORY_SEPARATOR . 'assets\\' . DIRECTORY_SEPARATOR . '.*$/';
    	$regex_iterator = new \RegexIterator($recursive_iterator_iterator, $pattern, \RecursiveRegexIterator::GET_MATCH);
    	$assets = array();
    	foreach ($regex_iterator as $file) {
    		if (is_file($file[0]) &&
    			is_readable($file[0]) &&
    			strtolower(substr($file[0], -4)) != '.php' &&
    			strtolower(substr($file[0], -6)) != '.phtml'
    		) {
        		$assets[] = realpath($file[0]);
    		}
    	}
        sort($assets);

    	$report = '';
    	$report .= '<br />Assets found in the project<br /><br />';
        $report .= '<table><thead><th>Filesystem paths of assets found in modules / bundles</th><th class="tac kb">Asset size in KB</th><th class="mtime tac">Server last modification time</th></thead><tbody>';
        foreach ($assets as $file) {
            $report .= '<tr><td>' . $file . '</td><td class="tac">' . $this->kb(filesize($file)) . ' KB</td><td class="tac">' . date('Y-m-d H:i:s', filemtime($file)) . '</td></tr>';
        }
        $report .= '</tbody></table>';

    	/* instalamos los assets */

    	$report .= '<br />Installing...<br /><br />';
    	$delimiter = DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
        $assets_installed = array();

        $assets_web_relative = array();

    	foreach ($assets as $i => $file) {
    		$parts = explode($delimiter, $file);
    		$parts_length = count($parts);
    		// part -2: the namespace
    		$namespace = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', basename($parts[$parts_length-2])));
    		// part -1: the subfolder
    		$relative_file_path = $parts[$parts_length-1];

            $assets_web_relative[$i] = '/assets/' . $namespace . '/' . $relative_file_path;
            $assets_web_relative[$i] = str_replace('\\', '/', $assets_web_relative[$i]);
            $assets_web_relative[$i] = str_replace('//', '/', $assets_web_relative[$i]);

    		$absolute_file_path = $public_directory . '/assets/' . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $relative_file_path;
    		$absolute_directory = dirname($absolute_file_path);
    		if (!file_exists($absolute_directory)) {
    			mkdir($absolute_directory, 0755, true);
    		}
    		copy($file, $absolute_file_path);
            $assets_installed[] = $absolute_file_path;
    	}

        $report .= '<b>Ok!</b><br /><br />';

        $public_assets_directory = realpath($public_directory . '/assets/');

/*
        $report .= 'Assets installed in <b>' . $public_assets_directory . '</b><br /><br />';
        $report .= '<table><thead><th>Filesystem paths of assets installed in the web / public directory of your project</th><th>URL paths of assets installed</th><th class="tac kb">Asset size in KB</th><th class="mtime tac">Server last modification time</th></thead><tbody>';
        foreach ($assets_installed as $i => $file) {
            $report .= '<tr><td>' . realpath($file) . '</td><td><a href="' . $assets_web_relative[$i] . '">' . $assets_web_relative[$i] . '</a></td><td class="tac">' . $this->kb(filesize($file)) . ' KB</td><td>' . date('Y-m-d H:i:s', filemtime($file)) . '</td></tr>';
        }
        $report .= '</tbody></table>';
*/

        $report .= 'Assets installed in <b>' . $public_assets_directory . '</b><br /><br />';
        $report .= '<table><thead><th>URL paths of assets installed</th><th class="tac kb">Asset size in KB</th><th class="mtime tac">Server last modification time</th></thead><tbody>';
        foreach ($assets_installed as $i => $file) {
            $report .= '<tr><td><a href="' . $assets_web_relative[$i] . '">' . $assets_web_relative[$i] . '</a></td><td class="tac">' . $this->kb(filesize($file)) . ' KB</td><td class="tac">' . date('Y-m-d H:i:s', filemtime($file)) . '</td></tr>';
        }
        $report .= '</tbody></table>';

        $this->layout()->config = $this->config;

		return new ViewModel(array('report' => $report));

    }


    protected function kb ($bytes)
    {
        $kb = number_format(round($bytes / 1024, 1), 1, '.', ',');
        return $kb;
    }

}
