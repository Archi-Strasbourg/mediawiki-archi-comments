<?php
/**
 * SpecialArchiComments class
 */
namespace ArchiComments;

/**
 * SpecialPage Special:ArchiComments that lists recent comments
 */
class SpecialArchiComments extends \SpecialPage
{
    /**
     * SpecialArchiComments constructor
     */
    public function __construct()
    {
        parent::__construct('ArchiComments');
    }

    /**
     * Display the special page
     * @return void
     */
    public function execute()
    {
        $output = $this->getOutput();
        $this->setHeaders();

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            ['Comments', 'page'],
            ['Comment_Page_ID', 'Comment_Date', 'Comment_Text'],
            'page_id IS NOT NULL',
            null,
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
            $title = \Title::newFromId($row->Comment_Page_ID);
            $output->addWikiText('=== '.preg_replace('/\(.*\)/', '', $title->getText()).' ==='.PHP_EOL);
            $output->addHTML(\ArchiHome\SpecialArchiHome::getCategoryTree($title));
            $wikitext = "''".strtok(wordwrap($row->Comment_Text, 170, '…'.PHP_EOL), PHP_EOL)."''".PHP_EOL.PHP_EOL.
                '[['.$title->getFullText().'#Commentaires|Consulter le commentaire]]';
            $output->addWikiText($wikitext);
            $output->addHTML('<div style="clear:both;"></div>');
        }
    }

    /**
     * Return the special page category
     * @return string
     */
    public function getGroupName()
    {
        return 'pages';
    }
}
