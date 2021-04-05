<?php

declare(strict_types=1);

namespace Local\Bundles\UrlSignedBundle\DependencyInjection\Compiler;

use Local\Bundles\UrlSignedBundle\UrlSigner\UrlSignerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class SignerPass
 * @package Local\Bundles\UrlSignedBundle\DependencyInjection\Compiler
 */
final class SignerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     * @param ContainerBuilder $container Контейнер.
     */
    public function process(ContainerBuilder $container): void
    {
        $signers = $this->getSigners($container);
        /** @var string $signerName */
        $signerName = $container->getParameter('url_signer.signer');
        $availableNames = [];

        foreach ($signers as $name => $signerId) {
            if ($name === $signerName) {
                $container->setAlias('url_signer.signer', $signerId);
                $container->setAlias(UrlSignerInterface::class, $signerId);

                return;
            }

            $availableNames[] = $name;
        }

        throw new InvalidArgumentException(sprintf("No URL signer with the name \"%s\" found. Available names are:\n%s",
            $signerName, implode("\n", array_map(static function (string $availableName) {
                return sprintf('- "%s"', $availableName);
            }, $availableNames))));
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array<string, string>
     */
    private function getSigners(ContainerBuilder $container): array
    {
        $signers = [];

        /** @var array<string, string[]> $signerServices */
        $signerServices = $container->findTaggedServiceIds('url_signer.signer');
        foreach ($signerServices as $signerServiceId => $signerServiceTags) {
            $signerServiceDefinition = $container->getDefinition($signerServiceId);

            $signerServiceDefinition->setArgument('$signatureKey', '%url_signer.signature_key%');
            $signerServiceDefinition->setArgument('$defaultExpiration', '%url_signer.default_expiration%');
            $signerServiceDefinition->setArgument('$expiresParameter', '%url_signer.expires_parameter%');
            $signerServiceDefinition->setArgument('$signatureParameter', '%url_signer.signature_parameter%');

            /** @var class-string<UrlSignerInterface> $signerServiceClass */
            $signerServiceClass = $signerServiceDefinition->getClass();

            $signers[$signerServiceClass::getName()] = $signerServiceId;
        }

        return $signers;
    }
}
