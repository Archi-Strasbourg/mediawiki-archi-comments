<?php
/**
 * SpecialArchiComments class.
 */

namespace ArchiComments;

use ArchiHome\SpecialArchiHome;
use DateTime;
use Exception;
use MediaWiki\MediaWikiServices;
use Title;
use User;

/**
 * SpecialPage Special:ArchiComments that lists recent comments.
 */
class SpecialArchiComments extends \SpecialPage
{
    /**
     * SpecialArchiComments constructor.
     */
    public function __construct()
    {
        parent::__construct('ArchiComments');
    }

    /**
     * Display the special page.
     *
     * @param string $subPage
     *
     * @return void
     * @throws Exception
     */
    public function execute($subPage)
    {
        $output = $this->getOutput();
        $this->setHeaders();
        $services = MediaWikiServices::getInstance();

        $dbr = $services->getDBLoadBalancer()->getConnection(DB_REPLICA);
        $res = $dbr->select(
            ['Comments', 'page'],
            ['Comment_Page_ID', 'Comment_Date', 'Comment_Text', 'Comment_actor'],
            'page_id IS NOT NULL',
            __METHOD__,
            ['ORDER BY' => 'Comment_Date DESC'],
            [
                'page' => [
                    'LEFT JOIN', 'Comment_Page_ID = page_id',
                ],
            ]
        );

        foreach ($res as $row) {
            if ($res->key() > 20) {
                break;
            }
            $user = $services->getUserFactory()
                ->newFromActorId($row->Comment_actor);
            $date = new DateTime($row->Comment_Date);
            $title = Title::newFromId($row->Comment_Page_ID);
            $output->addWikiTextAsContent('=== ' . preg_replace('/\(.*\)/', '', $title->getText()) . ' ===' . PHP_EOL);
            $output->addHTML(SpecialArchiHome::getCategoryTree($title));
            $output->addWikiTextAsInterface('Par [[Utilisateur:' . $user->getName() . '|' . $user->getName() . ']] le ' . $date->format('d/m/Y'));
            $wikitext = "''" . strtok(wordwrap($row->Comment_Text, 170, '…' . PHP_EOL), PHP_EOL) . "''" . PHP_EOL . PHP_EOL .
                '[[' . $title->getFullText() . '#' . wfMessage('comments')->parse() . '|' . wfMessage('seecomment')->parse() . ']]';
            $output->addWikiTextAsContent($wikitext);
            $output->addHTML('<div style="clear:both;"></div>');
        }
    }

    /**
     * Return the special page category.
     *
     * @return string
     */
    public function getGroupName(): string
    {
        return 'pages';
    }
}
