<?php
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpRequestValidator;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\App\Request\Http;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;

/**
 * Validator to check HTTP verb for Graphql requests
 */
class HttpVerbValidator implements HttpRequestValidatorInterface
{
    /**
     * Check if request is using correct verb for query or mutation
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlInputException
     */
    public function validate(HttpRequestInterface $request) : void
    {
        /** @var Http $request */
        if (false === $request->isPost()) {
            $query = $request->getParam('query', '');
            // The easiest way to determine mutations without additional parsing
            if (strpos(trim($query), 'mutation') === 0) {
                throw new GraphQlInputException(
                    new \Magento\Framework\Phrase('Mutation requests allowed only for POST requests')
                );
            }
        }
    }
}
