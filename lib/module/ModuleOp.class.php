<?php
/**
 * Functions for OPs
 *
 * @author	Tim Düsterhus
 * @copyright	2010 - 2011 Tim Düsterhus
 */
class ModuleOp extends Module {
	public function destruct() {

	}
	public function handle(Bot $bot) {
		if (Module::removeWhisper($bot->message['text']) == '!shutdown') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'op.shutdown')) return $bot->denied();
			Core::log()->info = $bot->message['usernameraw'].' shutted the bot down';
			$bot->shutdown();
		}
		else if (Module::removeWhisper($bot->message['text']) == '!restart') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'op.shutdown')) return $bot->denied();
			Core::log()->info = $bot->message['usernameraw'].' restarted the bot';
			$bot->shutdown(SIGUSR1);
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 5) == '!say ') {
			if (!Core::compareLevel($bot->lookUpUserID(), 500)) return $bot->denied();
			$parts = explode(' ', substr(Module::removeWhisper($bot->message['text']), 5), 2);
			if (!is_numeric($parts[0])) {
				$roomID = null;
				$message = implode(' ', $parts);
			}
			else {
				list($roomID, $message) = $parts;
			}
			$bot->queue($message, $roomID);
		}
		else if (Module::removeWhisper($bot->message['text']) == '!loaded') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'op.load')) return $bot->denied();
			$bot->queue('/whisper '.$bot->message['usernameraw'].', '.Core::language()->op_loaded.': '.implode(', ', array_keys(Core::getModules())));
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 6) == '!join ') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'op.join')) return $bot->denied();
			$bot->getConnection()->join(substr(Module::removeWhisper($bot->message['text']), 6));
			$bot->success();
		}
		else if (Module::removeWhisper($bot->message['text']) == '!perms') {
			if (!Core::compareLevel($bot->lookUpUserID(), 500)) return $bot->denied();
			$perms = Core::permission()->getNodes();
			ksort($perms);
			$permString = array();
			foreach ($perms as $name => $level) {
				$permString[] = $name.': '.$level;
			}
			$bot->queue('/whisper '.$bot->message['usernameraw'].', '.Core::language()->op_perms.': '.implode(', ', $permString));
		}
		else if (Module::removeWhisper($bot->message['text']) == '!temproom') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'op.join')) return $bot->denied();
			$bot->getConnection()->postMessage($bot->message['roomID'].' /temproom');
			$rooms = array_keys($bot->getConnection()->getRooms());
			$room = end($rooms);
			$bot->getConnection()->join($room);
		}
		else if (Module::removeWhisper($bot->message['text']) == '!rooms') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'op.join')) return $bot->denied();
			$rooms = $bot->getConnection()->getRooms();
			$roomString = array();
			foreach ($rooms as $id => $name) {
				$roomString[] = $name.': '.$id;
			}
			$bot->queue('/whisper '.$bot->message['usernameraw'].', '.Core::language()->op_rooms.': '.implode(', ', $roomString));
		}
		else if (preg_match('/^!(mute|ban|gmute|gban)/', Module::removeWhisper($bot->message['text']))) {
			if (!Core::compareLevel($bot->lookUpUserID(), 'op.moderate')) return $bot->denied();
			Core::log()->moderate = $bot->message['usernameraw'].': '.Module::removeWhisper($bot->message['text']);
			$bot->queue('/'.substr(Module::removeWhisper($bot->message['text']), 1));
		}
	}
}

