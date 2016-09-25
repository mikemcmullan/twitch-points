
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
		return `<img class="emoticon" src="http://cdn.betterttv.net/emote/${emoteId}/1x">`;
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

	formatMessage(message) {
		this.globalEmotes.forEach((emote) => {
			message = message.replace(emote.code, this.makeImage(emote.id));
		});

		this.channelEmotes.forEach((emote) => {
			message = message.replace(emote.code, this.makeImage(emote.id));
		});

		return message;
	}
}
