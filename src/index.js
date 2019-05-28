import Player from './player'

window.onload = () => {
  const soundBtn = document.getElementById('sound-btn')
  const rawTdpOptions = document.querySelector('.sound-options')
  if (soundBtn && rawTdpOptions && rawTdpOptions.value) {
    // Load the sounds
    const tdpOptions = JSON.parse(rawTdpOptions.value)
    const playlist = Object.keys(tdpOptions).map(title => ({
      title,
      file: tdpOptions[title],
      howl: null
    }))

    // Remove the DOM element containing the sounds options
    rawTdpOptions.parentNode.removeChild(rawTdpOptions)

    const player = new Player(playlist, icon => {
      soundBtn.classList.remove('loading', 'speaker', 'speaker-mute')
      soundBtn.classList.add(icon)
    })

    soundBtn.onclick = () => {
      if (!player.playing()) player.play()
      else player.pause()
    }
  }
}
