<?php

namespace ZOTORN\EmailObfuscator;

class EmailLink
{
    protected $link = '';
    protected $preHREF = '';
    protected $email = '';
    protected $postHREF = '';
    protected $linkText = '';

    protected $emailLinkPattern = '/^<a(.+?)href=[\'"]mailto:([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})[\'"](.*?)>(.*?)<\/a>$/i';

    public function setLink($link) {
        if ($this->isValid($link)) {
            $this->link = $link;
            $this->parse();
        } else {
            throw new InvalidLinkException('The link provided is not valid. Given: ' . $link);
        }
    }

    public function __construct($link) {
        $this->setLink($link);
    }

    protected function isValid($link) {
        if (preg_match($this->getEmailLinkPattern(), $link)) {
            return TRUE;
        }
        return FALSE;
    }

    protected function parse() {
        preg_match_all($this->getEmailLinkPattern(), $this->link, $matches);
        $this->preHREF = trim($matches[1][0]);
        $this->email = trim($matches[2][0]);
        $this->postHREF = trim($matches[3][0]);
        $this->linkText = trim($matches[4][0]);
    }

    public function getPreHREF() {
        return $this->preHREF;
    }

    public function getPostHREF() {
        return $this->postHREF;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getLinkText() {
        return $this->linkText;
    }

    public function getEmailLinkPattern() {
        return $this->emailLinkPattern;
    }

}
