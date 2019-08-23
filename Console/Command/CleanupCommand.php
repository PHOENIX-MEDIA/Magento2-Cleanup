<?php
/**
 * PHOENIX MEDIA - Cleanup
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to license that is bundled with
 * this package in the file LICENSE.
 *
 * @category   Phoenix
 * @package	   Phoenix_Cleanup
 * @copyright  Copyright (c) 2013-2019 PHOENIX MEDIA GmbH (http://www.phoenix-media.eu)
 */
namespace Phoenix\Cleanup\Console\Command;

use Phoenix\Cleanup\Model\Cron;
use Phoenix\Cleanup\Model\Handler\Resolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    const PARAM_HANDLER = 'handler';

    /**
     * @var Cron
     */
    protected $cron;

    /**
     * @var Resolver
     */
    protected $handlerResolver;


    /**
     * CleanupCommand constructor.
     *
     * @param Cron $cron
     * @param Resolver $handlerResolver
     */
    public function __construct(
        Cron $cron,
        Resolver $handlerResolver
    ) {
        $this->cron = $cron;
        $this->handlerResolver = $handlerResolver;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('phoenix:cleanup:run')
            ->setDescription('Run cleanup handler')
            ->setDefinition(
                [
                    new InputArgument(
                        self::PARAM_HANDLER,
                        InputArgument::OPTIONAL,
                        'Handler name, possible values: '.implode(', ', $this->handlerResolver->getHandlers())
                    )
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($handlerKey = $input->getArgument(self::PARAM_HANDLER)) {
            $handler = $this->handlerResolver->get($handlerKey);
            if ($handler->isEnabled()) {
                $handler->cleanup();
            }
        } else {
            $res = $this->cron->execute();
            $output->writeln($res);
        }

        $output->writeln('Completed.');
    }
}
