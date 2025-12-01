class AudioManager {
  constructor() {
    this.sounds = {};
    this.bgm = null;
    this.bgmPlaying = false;
  }

  preload(name, src, volume = 1.0) {
    const audio = new Audio(src);
    audio.volume = volume;
    this.sounds[name] = audio;
  }

  play(name) {
    const sound = this.sounds[name];
    if (!sound) return;

    sound.currentTime = 0;
    sound.play();
  }

  setBGM(src, volume = 0.4) {
    this.bgm = new Audio(src);
    this.bgm.loop = true;
    this.bgm.volume = volume;
  }

  playBGM() {
    if (!this.bgmPlaying && this.bgm) {
      this.bgm.play();
      this.bgmPlaying = true;
    }
  }

  stopBGM() {
    if (this.bgmPlaying && this.bgm) {
      this.bgm.pause();
      this.bgmPlaying = false;
    }
  }
}

window.audio = new AudioManager();
