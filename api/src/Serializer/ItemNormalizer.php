<?php

namespace App\Serializer;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use ApiPlatform\Metadata\Util\ClassInfoTrait;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Decorates the HAL item normalizer to add links for operations.
 * 
 * API platform's default HAL item normalizer does not include links for operations,
 * only for linked resources.
 * 
 * @todo decorate as well the collection normalizer
 */
#[AsDecorator('api_platform.hal.normalizer.item')]
class ItemNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use ClassInfoTrait;
    
    public function __construct(
        private NormalizerInterface $normalizer,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly IdentifiersExtractorInterface $identifiersExtractor,
        private readonly RouterInterface $router
    ) {}

    // NormalizerInterface

    public function getSupportedTypes(?string $format = null): array
    {
        return $this->normalizer->getSupportedTypes($format);
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        if (isset($data['_links'])) {
            // Iterate over #ApiResource() attributes for the object
            // (as a single object can have multiple ApiResource attributes)
            $resourceMetadataCollection = $this->resourceMetadataCollectionFactory->create($this->getObjectClass($object));
            foreach($resourceMetadataCollection->getIterator() as $resourceMetadata) {
                // Iterate over item HTTP operations defined in the ApiResource attribute
                foreach($resourceMetadata->getOperations()->getIterator() as $operationName => $operation) {
                    if (
                        is_string($operationName) &&
                        $operation instanceof HttpOperation &&
                        ($extraProperties = $operation->getExtraProperties()) &&
                        ($extraProperties['showAsItemlink'] ?? false)
                    ) {
                        $identifiers = $this->identifiersExtractor->getIdentifiersFromItem($object, $operation, $context);
                        $routeName = $operation->getRouteName() ?? $operation->getName();
                        $href = $this->router->generate($routeName, $identifiers, UrlGeneratorInterface::ABS_PATH);

                        $data['_links'][$operationName] = [
                            'href' => $href,
                        ];
                    }
                }
            }
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $this->normalizer->supportsNormalization($data, $format, $context);
    }

    // SerializerAwareInterface

    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->normalizer instanceof SerializerAwareInterface) {
            $this->normalizer->setSerializer($serializer);
        }
    }
}
