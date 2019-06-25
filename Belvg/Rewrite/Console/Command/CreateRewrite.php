<?php

namespace Belvg\Rewrite\Console\Command;

use Symfony\Component\Console\{
    Input\InputInterface,
    Output\OutputInterface,
    Command\Command,
    Input\InputArgument
};
use Magento\UrlRewrite\Model\{
    UrlRewriteFactory,
    ResourceModel\UrlRewrite
};
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

/**
 * Class CreateRewrite
 * @package Belvg\Rewrite\Console\Command
 */
class CreateRewrite extends Command
{
    const OldUrlArgument = 'old_url';
    const NewUrlArgument = 'new_url';
    const RedirectCodeArgument = 'redirect_code';

    private $urlRewriteFactory;
    private $urlRewriteResource;
    private $storeManager;

    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewrite $urlRewriteResource,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct('belvg:redirect:create');

        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewriteResource = $urlRewriteResource;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Add new redirect')
            ->setDefinition($this->getInputList());
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oldUrl = $input->getArgument(self::OldUrlArgument);
        $newUrl = $input->getArgument(self::NewUrlArgument);
        $redirectCode = $input->getArgument(self::RedirectCodeArgument);

        $newRewrite = $this->urlRewriteFactory->create();
        $newRewrite->setTargetPath($newUrl);
        $newRewrite->setRequestPath($oldUrl);
        $newRewrite->setEntityId(0);
        $newRewrite->setEntityType(Rewrite::ENTITY_TYPE_CUSTOM);
        $newRewrite->setStoreId( $this->storeManager->getStore()->getId());
        if($redirectCode) $newRewrite->setRedirectType((int)$redirectCode);

        try {
            $this->urlRewriteResource->save($newRewrite);
        } catch(AlreadyExistsException $e) {
            $output->writeln($e->getMessage());
        } catch(\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }

    /**
     * Get list of options and arguments for the command
     *
     * @return mixed
     */
    public function getInputList()
    {
        return [
            new InputArgument(
                self::OldUrlArgument,
                InputArgument::REQUIRED,
                'Old Url'
            ),
            new InputArgument(
                self::NewUrlArgument,
                InputArgument::REQUIRED,
                'New Url'
            ),
            new InputArgument(
                self::RedirectCodeArgument,
                InputArgument::OPTIONAL,
                'Redirect Code'
            ),
        ];
    }
}
