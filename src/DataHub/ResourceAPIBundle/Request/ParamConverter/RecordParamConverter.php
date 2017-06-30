<?php

namespace DataHub\ResourceAPIBundle\Request\ParamConverter;

use DataHub\ResourceAPIBundle\Document\Record;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ParamConverter for Record type document
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceAPIBundle
 */
class RecordParamConverter implements ParamConverterInterface
{
    /**
     * @var ManagerRegistry $registry Manager registry
     */
    private $registry;

    /**
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $recordIds = $request->attributes->get('recordIds');
        $entityManager = $this->registry->getManagerForClass($configuration->getClass());
        $recordsRepository = $entityManager->getRepository($configuration->getClass());

        $record = $recordsRepository->findOneByProperty('recordIds', $recordIds);

        if (!$record instanceof Record) {
            throw new NotFoundHttpException(sprintf('Record could not be found.', $configuration->getClass()));
        }

        $request->attributes->set($configuration->getName(), $record);
    }
}
