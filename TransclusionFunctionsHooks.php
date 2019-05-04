<?php
use MediaWiki\MediaWikiServices;

class TransclusionFunctionsHooks {
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'maptemplate', [ self::class, 'renderMapTemplate' ]);
		$parser->setFunctionHook( 'frametitle', [ self::class, 'renderFrameTitle' ], SFH_OBJECT_ARGS);
		$parser->setFunctionHook( 'ifembeds', [ self::class, 'renderIfEmbeds' ]);
	}

	/* Parser function to split a string and pass each part as first parameter to a given template. */
	public static function renderMapTemplate( Parser $parser, $text, $template, $insep='', $outsep='', $userparam='') {
		if (empty($text))
			return '';

		$insep  = str_replace('\n', "\n", $parser->mStripState->unstripNoWiki($insep));
		$outsep = str_replace('\n', "\n", $parser->mStripState->unstripNoWiki($outsep));
		$output = '';

		if (empty($insep))
			$insep = ' ';

		$values = explode($insep, $text);

		foreach ($values as $idx=>$val){
			$output .= $parser->replaceVariables("{{{$template}|1=$val|#=$idx|#userparam=$userparam}}");
			if ($idx != count($values)-1)
				$output .= $outsep;
		}
		return [$output, []];
	}

	/* Parser function to allow templates to access their own title or the title of a parent. */
	public static function renderFrameTitle( Parser $parser, $frame, $args) {
		$level = intval($args[0]); // 0 is own title
		while ($level-- > 0){
			if ($frame->parent)
				$frame = $frame->parent;
			else
				return "";
		}
		return $frame->getTitle();
	}

	public static function renderIfEmbeds( Parser $parser, $tpl, $yes='', $no='') {
		# we need a second hook to detect templates after the parser function
		$checks = $parser->getOutput()->getExtensionData('TransclusionFunctions_ifembeds') ?? [];
		$checks[] = [Title::newFromText($tpl, NS_TEMPLATE), $yes, $no];
		$parser->getOutput()->setExtensionData('TransclusionFunctions_ifembeds', $checks);
	}

	public static function onContentAlterParserOutput( $content, $title, $output ) {
		$checks = $output->getExtensionData('TransclusionFunctions_ifembeds');
		foreach ($checks as $check){
			list($title, $yes, $no) = $check;
			$code = isset($output->getTemplates()[$title->getNamespace()][$title->getDBKey()]) ? $yes : $no;
			$p = MediaWikiServices::getInstance()->getParser();
			$p->parse($code, $title, new ParserOptions());
			$output->mergeTrackingMetaDataFrom($p->getOutput());
		}
	}
}
