<?php

namespace Charlyp\AutocompleteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DemoController
{
    private $templating;

    public function __construct(EngineInterface $engine)
    {
        $this->templating = $engine;
    }

    public function autocompleteAction()
    {
        return new Response('OK');
    }

    public function getJSONAction()
    {
        return new JsonResponse();
    }
}
