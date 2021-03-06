<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\Constraints\IdenticalTo;

class ParamFetcherController extends FOSRestController
{
    /**
     * @RequestParam(name="raw", requirements=@IdenticalTo({"foo"="raw", "bar"="foo"}), default="invalid")
     * @RequestParam(name="map", map=true, requirements=@IdenticalTo({"foo"="map", "foobar"="foo"}), default="%invalid% %%")
     * @RequestParam(name="bar", map=true, requirements="%foo% foo", strict=true)
     */
    public function paramsAction(ParamFetcherInterface $fetcher)
    {
        return new JsonResponse($fetcher->all(false));
    }

    /**
     * @QueryParam(name="foo", default="invalid")
     * @RequestParam(name="bar", default="foo")
     */
    public function testAction(Request $request, ParamFetcherInterface $fetcher)
    {
        $paramsBefore = $fetcher->all();

        $newRequest = new Request();
        $newRequest->query = $request->query;
        $newRequest->request = $request->request;
        $newRequest->attributes->set('_controller', sprintf('%s::paramsAction', __CLASS__));
        $response = $this->container->get('http_kernel')->handle($newRequest, HttpKernelInterface::SUB_REQUEST, false);

        $paramsAfter = $fetcher->all(false);

        return new JsonResponse(array(
            'before' => $paramsBefore,
            'during' => json_decode($response->getContent(), true),
            'after' => $paramsAfter,
        ));
    }
}
