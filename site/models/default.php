<?php

class DefaultPage extends Page
{
    // Checks if the current user is allowed to read the page
    public function isReadable(): bool
    {
        // Grant access if the current user is an admin, as admins can read all pages
        if ($this->kirby()->user()->isAdmin()) {
            return true;
        }

        // Moderators should be able to access static pages
        if (Str::endsWith($this->intendedTemplate()->name(), '_static') && $this->kirby()->user()->role()->name() == 'ModUser') {
            return true;
        }

        // Grant access if the current user is the author of the page
        if (($user = $this->author()->toUser()) && $user->is($this->kirby()->user())) {
            return true;
        }

        // Check if the current user is listed as a co-author of the page
        if (($user = $this->coauthor()->toUser()) && $user->is($this->kirby()->user())) {
            return true;
        }

        // If none of the above conditions are met, the page is not readable
        return false;
    }
}

