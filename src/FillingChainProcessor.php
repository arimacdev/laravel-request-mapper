<?php
declare(strict_types = 1);

namespace Maksi\LaravelRequestMapper;

use Illuminate\Http\Request;
use Maksi\LaravelRequestMapper\Exception\HandlerNotFoundException;
use Maksi\LaravelRequestMapper\Filling\RequestData\RequestData;
use Maksi\LaravelRequestMapper\Filling\Strategies\StrategyInterface;
use Maksi\LaravelRequestMapper\Validation\Data\ValidateData;
use Maksi\LaravelRequestMapper\Validation\ValidationProcessor;

/**
 * Class FillingChainProcessor
 *
 * @package Maksi\LaravelRequestMapper
 */
class FillingChainProcessor
{
    /**
     * @var ValidationProcessor
     */
    private $validationHandler;

    /**
     * @var array|StrategyInterface[]
     */
    private $strategies = [];

    /**
     * StrategiesHandler constructor.
     *
     * @param ValidationProcessor $validationHandler
     */
    public function __construct(ValidationProcessor $validationHandler)
    {
        $this->validationHandler = $validationHandler;
    }

    /**
     * @param StrategyInterface $strategy
     *
     * @return $this
     */
    public function addStrategy(StrategyInterface $strategy): self
    {
        $this->strategies[] = $strategy;

        return $this;
    }

    /**
     * @param RequestData $object
     *
     * @throws Validation\ResponseException\AbstractException
     */
    public function handle(RequestData $object, Request $request): void
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($request, $object)) {
                $data = $strategy->resolve($request);
                $this->validationHandler->validateBeforeFilling(new ValidateData($object, $data));
                $object->__construct($data);
                $this->validationHandler->validateAfterFilling(new ValidateData($object, $data));

                return;
            }
        }

        throw new HandlerNotFoundException(
            sprintf('no handler found for %s class', \get_class($object))
        );
    }
}
