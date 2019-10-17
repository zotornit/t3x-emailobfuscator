<?php

namespace ZOTORN\EmailObfuscator;

class EmailPlain extends EmailLink
{
    protected $link = '';
    protected $email = '';

    protected $emailLinkPattern = '#([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})#i';

    public function setLink($link) {
        if ($this->isValid($link)) {
            $this->link = $link;
            $this->parse();
        } else {
            throw new InvalidLinkException('The plain email provided is not valid. Given: ' . $link);
        }
    }

    public function __construct($link) {
        $this->setLink($link);
    }

//    protected function validate($link) {
//        if (preg_match($this->getEmailLinkPattern(), $link)) {
//            return TRUE;
//        }
//        return FALSE;
//    }

    protected function isValid($link) {
        if (preg_match($this->getEmailLinkPattern(), $link)) {
            return TRUE;
        }
        return FALSE;
    }

    protected function parse() {
        preg_match_all($this->getEmailLinkPattern(), $this->link, $matches);
        $this->email = trim($matches[1][0]);
    }

    public function getEmail() {
        return $this->email;
    }

    public function getEmailLinkPattern() {
        return $this->emailLinkPattern;
    }

}
