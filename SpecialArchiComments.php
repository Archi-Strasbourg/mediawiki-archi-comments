<?php

namespace ArchiComments;

class SpecialArchiComments extends \SpecialPage
{
    public function __construct()
    {
        parent::__construct('ArchiComments');
    }

    public function execute($par)
    {
        $output = $this->getOutput();
        $this->setHeaders();

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->query(
            'SELECT Comment_Page_ID, Comment_Date, Comment_Text
            FROM (
                SELECT Comment_Page_ID, Comment_Date, Comment_Text
                FROM Comments
                ORDER BY Comment_Date DESC
            ) as Comments
            LEFT JOIN `page` ON ((Comment_Page_ID = page_id))
            WHERE (page_id IS NOT NULL)
            GROUP BY Comment_Page_ID
            ORDER BY Comment_Date DESC
            LIMIT 10;'
        );

        foreach ($res as $row) {
            $title = \Title::newFromId($row->Comment_Page_ID);
            $output->addWikiText('=== '.preg_replace('/\(.*\)/', '', $title->getText()).' ==='.PHP_EOL);
            $output->addHTML(\ArchiHome\SpecialArchiHome::getCategoryTree($title));
            $wikitext = "''".strtok(wordwrap($row->Comment_Text, 170, '…'.PHP_EOL), PHP_EOL)."''".PHP_EOL.PHP_EOL.
                '[['.$title->getFullText().'#Commentaires|Consulter le commentaire]]';
            $output->addWikiText($wikitext);
            $output->addHTML('<div style="clear:both;"></div>');
        }
    }

    public function getGroupName()
    {
           return 'pages';
    }
}
