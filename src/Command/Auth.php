<?php

declare(strict_types=1);

namespace Edspc\OauthHttpClient\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ServiceLocator;

class Auth extends Command
{
    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;

        parent::__construct('edspc:oauth-client:auth');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Get Access Token by Grant one.')
            ->addOption(
                'auth',
                'a',
                InputOption::VALUE_REQUIRED,
                'Auth provider. Possible values: '.\implode(
                    ',',
                    \array_keys($this->locator->getProvidedServices())
                )
            )
            ->addOption(
                'grantToken',
                'g',
                InputOption::VALUE_REQUIRED,
                'Grant Token'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $authProcessor = $input->getOption('auth');
        $grantToken = $input->getOption('grantToken');

        if (!$authProcessor) {
            $authProcessor = $io->askQuestion(
                new ChoiceQuestion(
                    'What auth processor should use?', \array_keys($this->locator->getProvidedServices())
                )
            );
        }
        if (!$grantToken) {
            $grantToken = $io->askQuestion(
                new Question(
                    'Enter Grant Token?'
                )
            );
        }
        $this->locator->get($authProcessor)->authByGrantToken($grantToken)
        ;
        $output->writeln('<info>Success.</info>');

        return Command::SUCCESS;
    }
}
