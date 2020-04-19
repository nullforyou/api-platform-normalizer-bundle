<?php

namespace Xiaokang\Bundle\ApiPlatformNormalizerBundle\Hydra\Serializer;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use ApiPlatform\Core\Problem\Serializer\ErrorNormalizerTrait;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ErrorNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    use ErrorNormalizerTrait;

    public const FORMAT = 'jsonld';
    public const TITLE = 'title';

    private $urlGenerator;
    private $debug;
    private $defaultContext = [self::TITLE => 'An error occurred'];

    public function __construct(UrlGeneratorInterface $urlGenerator, bool $debug = false, array $defaultContext = [])
    {
        $this->urlGenerator = $urlGenerator;
        $this->debug = $debug;
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            '@context' => $this->urlGenerator->generate('api_jsonld_context', ['shortName' => 'Error']),
            '@type' => 'hydra:Error',
            'hydra:title' => $context[self::TITLE] ?? $this->defaultContext[self::TITLE],
            'hydra:description' => $this->getErrorMessage($object, $context, $this->debug),
            'hydra:statusCode' => $object->getCode()
        ];

        if ($this->debug && null !== $trace = $object->getTrace()) {
            $data['trace'] = $trace;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return self::FORMAT === $format && ($data instanceof \Exception || $data instanceof FlattenException || $data instanceof LegacyFlattenException);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}