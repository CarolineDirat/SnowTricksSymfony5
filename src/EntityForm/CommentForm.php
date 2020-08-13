<?php

namespace App\EntityForm;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use DateTimeImmutable;

class CommentForm
{
    public function initialize(Trick $trick, ?User $user): Comment
    {
        $comment = new Comment();
        return $comment
            ->setTrick($trick)              
            ->setCreatedAt(new DateTimeImmutable())
            ->setUser($user);
    }
}
