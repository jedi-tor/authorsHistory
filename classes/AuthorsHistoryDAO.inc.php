<?php

/**
 * @file plugins/generic/AuthorsHistory/classes/AuthorsHistoryDAO.inc.php
 *
 * @class AuthorsHistoryDAO
 * @ingroup plugins_generic_authorsHistory
 *
 * Operations for retrieving authors data
 */

import('lib.pkp.classes.db.DAO');

class AuthorsHistoryDAO extends DAO {
    
    private function getAuthorsByORCID($orcid) {
        $resultAutorzinhos = $this->retrieve(
            "SELECT author_id FROM author_settings WHERE setting_name = 'orcid' AND setting_value = '{$orcid}'"
        );
        $autorzinhos = (new DAOResultFactory($resultAutorzinhos, $this, '_authorFromRow'))->toArray();

        return $autorzinhos;
    }

    private function getAuthorsByEmail($email) {
        $resultAutorzinhos = $this->retrieve(
            "SELECT author_id FROM authors WHERE email = '{$email}'"
        );
        $autorzinhos = (new DAOResultFactory($resultAutorzinhos, $this, '_authorFromRow'))->toArray();
        
        return $autorzinhos;
    }

    public function getAuthorsPublications($orcid, $email) {
        $authors = $this->getAuthorsByEmail($email);
        if($orcid) {
            $authorsFromOrcid = $this->getAuthorsByORCID($orcid);
            $authors = array_unique(array_merge($authors, $authorsFromOrcid));
        }

        $publicacoes = array();
        foreach ($authors as $autorId) {
            $author = DAOregistry::getDAO('AuthorDAO')->getById($autorId);
            $submission = DAORegistry::getDAO('SubmissionDAO')->getById($author->getSubmissionId());

            $publicacoes[] = $submission->getCurrentPublication();
        }

        return $publicacoes;
    }

    private function _authorFromRow($row) {
        return $row['author_id'];
    }
}