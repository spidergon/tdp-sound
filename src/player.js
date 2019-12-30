/**
 * Player class containing the state of our playlist and where we are in it.
 * Includes all methods for playing, skipping, updating the display, etc.
 * @param {Array} playlist Array of objects with playlist song details ({title, file, howl}).
 */
class Player {
  constructor(playlist, setIcon) {
    this.playlist = playlist
    this.setIcon = setIcon
    this.index = 0
    this.sound = null
  }

  /**
   * Check if a sound is currently playing or not.
   * @returns {Boolean} boolean
   */
  playing() {
    return this.sound && this.sound.playing()
  }

  /**
   * Play a song in the playlist.
   * @param {Number} index Index of the song in the playlist (leave empty to play the first or current).
   */
  play(index) {
    const newIndex = typeof index === 'number' ? index : this.index
    const data = this.playlist[newIndex]

    if (data.howl) {
      this.sound = data.howl
    } else if (data.file) {
      this.setIcon('loading')
      this.sound = data.howl = new Howl({
        src: [data.file],
        html5: true, // Force to HTML5 so that the audio can stream in (best for large files)
        onplay: () => this.setIcon('speaker'),
        onload: () => this.setIcon('speaker-mute'),
        onend: () => {
          this.setIcon('speaker-mute')
          this.skip()
        },
        onpause: () => this.setIcon('speaker-mute'),
        onstop: () => this.setIcon('speaker-mute')
      })
    }

    if (this.sound) {
      // Begin playing the sound
      this.sound.play()

      // Keep track of the index we are currently playing
      this.index = newIndex
    } else {
      console.log('no sound found.')
    }
  }

  /** Pause the currently playing track. */
  pause() {
    if (this.sound) this.sound.pause()
  }

  /**
   * Skip to the next or previous track.
   * @param {String} direction 'next' or 'prev'.
   */
  skip(direction) {
    // Get the next track based on the direction of the track
    let index = 0
    if (direction === 'prev') {
      index = this.index - 1
      if (index < 0) index = this.playlist.length - 1
    } else {
      index = this.index + 1
      if (index >= this.playlist.length) index = 0
    }
    this.skipTo(index)
  }

  /**
   * Skip to a specific track based on its playlist index.
   * @param {Number} index Index in the playlist.
   */
  skipTo(index) {
    // Stop the current track
    if (this.sound) this.sound.stop()

    // Play the new track
    this.play(index)
  }
} // End of class

export default Player
