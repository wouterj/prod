<?php

namespace Wj\DocguyBundle;

use Sculpin\Core\Sculpin;
use Sculpin\Core\Event\ConvertEvent;
use Sculpin\Bundle\TwigBundle\SculpinTwigBundle;
use Sculpin\Bundle\MarkdownBundle\SculpinMarkdownBundle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocguyFormatter implements EventSubscriberInterface
{
    public function compileBlocks(ConvertEvent $event)
    {
    }

    public function parseBlocks(ConvertEvent $event)
    {
    }

    public function parseCode(ConvertEvent $event)
    {
        if (!$event->isHandledBy(SculpinMarkdownBundle::CONVERTER_NAME, SculpinTwigBundle::FORMATTER_NAME)) {
            return;
        }

        $content = $event->source()->content();
        $newContent = preg_replace_callback('/^    \[(\w+)\]((?:\s{5}.+$)+)/m', function ($m) {
            return '~~~'.$m[1].PHP_EOL.trim(preg_replace('/^\s{4}/m', '', $m[2])).PHP_EOL.'~~~';
        }, $content);

        if ($newContent !== $content) {
            $event->source()->setContent($newContent);
        }
    }

    public function prettifyCodeBlocks(ConvertEvent $event)
    {
        if ($event->isHandledBy(SculpinMarkdownBundle::CONVERTER_NAME, SculpinTwigBundle::FORMATTER_NAME)) {
            $event->source()->setContent(preg_replace_callback('/<pre><code(?:([^>]+)class="(\w+)(.*?)")?/', function ($m) {
                if (1 === count($m)) {
                    return '<pre class="prettyprint linenums"><code';
                }
                return '<pre class="prettyprint linenums lang-'.$m[2].'"><code'.$m[1].'class="'.$m[3].'"';
            }, $event->source()->content()));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_CONVERT => array(array('compileBlocks', 0), array('parseCode', 1)),
            Sculpin::EVENT_AFTER_CONVERT => array(array('parseBlocks', 0), array('prettifyCodeBlocks', -99)),
        );
    }
}
