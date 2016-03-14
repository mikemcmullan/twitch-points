<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Redirect home with a message.
     *
     * @var $message string|null
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    protected function redirectHomeWithMessage($message = null)
    {
        return redirect()
            ->route('home_path', $this->getRouter()->current()->getParameter('channel')->slug)
            ->with('message', $message ? $message : 'You\'re not allowed to use this feature.');
    }
}
