<?php

namespace Wax\Shop\Events;

use App\Courseware\Models\Course;
use App\User;

class PurchasedCourse
{
    protected $course;
    protected $user;

    public function __construct(Course $course, User $user)
    {
        $this->course = $course;
        $this->user = $user;
    }

    public function course()
    {
        return $this->course;
    }

    public function user()
    {
        return $this->user;
    }
}
