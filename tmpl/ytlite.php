<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;


class plgContentYTL extends JPlugin
{
	public function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function onContentPrepare($context, &$article, &$params, $page = 0) {

		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}

		$app 	= Factory::getApplication();
		$view	= $app->input->get('view');
		if ($view == 'tag') { return; }

		$document		= Factory::getDocument();
		
		if ($document->getType() !== 'html' || !$app->isClient('site')) {
            return;
        }
		
		$lang 			= Factory::getLanguage();

		// Start Plugin
		$regex_ytl	='/{yt\/\s*(.*youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})\/?([^\/]*)\/?}/siU';
		$groups 	= array();
		$match		= preg_match($regex_ytl, $article->text, $groups);

		// Start if count_matches
		if ($match === 1) {

			/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
			$wa = $document->getWebAssetManager();
			$wa->registerAndUseStyle('plg_content_ytl', 'plg_content_ytl/lite-yt-embed.css');
			$wa->registerAndUseScript('plg_content_ytl', 'plg_content_ytl/lite-yt-embed.js', [], ['defer' => true]);
			
			while ($match === 1) { 
				 
				$video	= $groups[2];
				$url 	= $groups[1] . $video;
				$title	= $groups[3];
				$repl 	= '<lite-youtube videoid="' . $video . '" style="background-image: url(\'https://i.ytimg.com/vi/' . $video . '/maxresdefault.jpg\')"><a href="' . $url . '" class="lyt-playbtn" title="Play Video"><span class="lyt-visually-hidden">Play Video: ' . $title . '</span></a></lite-youtube>';

				$article->text 	= preg_replace($regex_ytl, $repl, $article->text, 1); 
				$match			= preg_match($regex_ytl, $article->text, $groups);
			}
			
		}// end if match
		return true;
	}
}
?>
