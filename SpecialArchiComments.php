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
        $res = $dbr->select(
            array('Comments', 'page'),
            array('Comment_Page_ID', 'Comment_Date', 'Comment_Text'),
            'page_id IS NOT NULL',
            null,
            array('ORDER BY'=>'Comment_Date DESC', 'LIMIT 20'),
            array(
                'page'=>array(
                    'LEFT JOIN', 'Comment_Page_ID = page_id'
                )
            )
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
