
export default class FormatTwitchEmotes {
	makeImage(emoteId) {
		return `<img class="emoticon" src="http://static-cdn.jtvnw.net/emoticons/v1/${emoteId}/1.0">`
	}

	parseEmotes(emotesString) {
		const emotes = [];

		emotesString.split('/').forEach((emoteString, index) => {
			emoteString = emoteString.split(':');

			const number = emoteString[0];

			emoteString[1].split(',').forEach((pos, index) => {
				const positions = pos.split('-');
				emotes.push({ emote: number, start: ~~positions[0], end: ~~positions[1] });
			});
		});

		return emotes.sort((a, b) => {
			return a.start - b.start;
		});
	}

	formatMessage(message, emotesString) {
		const messageParts = [];
		const emotes = this.parseEmotes(emotesString);

		emotes.forEach((emote, index, emotes) => {
			// If this is the first emote get the text before emote.
			if (index === 0) {
				messageParts.push(message.substr(0, emote.start).trim());
				messageParts.push(this.makeImage(emote.emote));
			}

			// Get the previous emote in the array and get the characters
			// between the end of that emote and the start of the current emote.
			if (emotes[index-1]) {
				const length = emote.start - (emotes[index-1].end+1);
				messageParts.push(message.substr(emotes[index-1].end+1, length).trim());
				messageParts.push(this.makeImage(emote.emote));
			}

			if (index === emotes.length-1) {
				messageParts.push(message.substr(emote.end+1).trim());
			}
		});

		return messageParts.join(' ');
	}
}
