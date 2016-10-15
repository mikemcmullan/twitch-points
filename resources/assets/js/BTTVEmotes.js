
export default class FormatBTTVEmotes {

	constructor() {
		this.globalEmotes = [];
		this.channelEmotes = [];
	}

	load(channel) {
		return Promise.all([
			this.getGlobalEmotes(),
			this.getChannelEmotes(channel)
		]);
	}

	makeImage(emoteId) {
		return `<img class="emoticon" src="//cdn.betterttv.net/emote/${emoteId}/1x">`;
	}

	getEmotes(url) {
		return new Promise((resolve, reject) => {
			var xhr = new XMLHttpRequest();

			xhr.timeout = 2000;
			xhr.open('GET', url, true);

			xhr.onreadystatechange = function () {
				if (this.readyState == 4 && this.status == 200) {
					const body = JSON.parse(this.responseText);

					resolve(body.emotes);
				}
			}

			xhr.ontimeout = function (e) {
				resolve([]);
			}

			xhr.send();
		});
	}

	getGlobalEmotes() {
		return this.getEmotes('https://api.betterttv.net/2/emotes')
			.then((emotes) => {
				this.globalEmotes = emotes;

				return emotes;
			});
	}

	getChannelEmotes(channel) {
		return this.getEmotes(`https://api.betterttv.net/2/channels/${channel}`)
			.then((emotes) => {
				this.channelEmotes = emotes;

				return emotes;
			});
	}

	shouldWeReplace(message, emote) {
		const startPos = message.indexOf(emote);

		// If the emote is not found at all.
		if (startPos === -1) {
			return false;
		}

		// The message only contained an emote.
		if (emote.length === message.length) {
			return true;
		}

		// The message starts with an emote.
		if (startPos === 0 && message[emote.length] === ' ') {
			return true;
		}

		// The message has an emote in the middle.
		if (message[startPos-1] === ' ' && message[startPos+emote.length] === ' ') {
			return true;
		}

		// The message has an emote at the end.
		if (message[startPos-1] === ' ' && message[startPos+emote.length] === undefined) {
			return true;
		}

		return false;
	}

	doReplace(message, emote) {
		let shouldContinue = true;

		while (shouldContinue) {
			if (this.shouldWeReplace(message, emote.code)) {
				message = message.replace(emote.code, this.makeImage(emote.id));
			} else {
				shouldContinue = false;
			}
		}

		return message;
	}

	formatMessage(message) {
		this.globalEmotes.forEach((emote) => {
			message = this.doReplace(message, emote);
		});

		this.channelEmotes.forEach((emote) => {
			message = this.doReplace(message, emote);
		});

		return message;
	}
}
