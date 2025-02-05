<?php
class DefaultPage extends Page
{
    // makes sure that a page is only readable by the author, coauthor or an admin
    public function isReadable(): bool
    {
        if (($user = $this->author()->toUser()) && $user->is($this->kirby()->user()) ||
            $this->kirby()->user()->isAdmin() || ($user = $this->coauthor()->toUser()) && $user->is($this->kirby()->user())) {
            return true;
        }

        return false;
    }
}
?>