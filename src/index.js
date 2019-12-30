import Player from './player'

/* eslint-disable no-extra-semi */
;(function($) {
  function animateSoundBtn(btn, icon) {
    if (!btn) return
    btn.classList.remove('loading', 'speaker', 'speaker-mute')
    btn.classList.add(icon)
  }

  function play(link, player, playlist) {
    if (!link || link.index() === -1) return
    link
      .addClass('active')
      .siblings()
      .removeClass('active')
    player.src = playlist[link.index()].file
    player.load()
    player.play()
  }

  const rawTdpOptions = document.querySelector('.sound-options')
  if (rawTdpOptions && rawTdpOptions.value) {
    const htmlPlaylist = $('#playlist')
    const audio = $('.playlist-container audio')
    // Load the sounds
    const tdpOptions = JSON.parse(rawTdpOptions.value)
    rawTdpOptions.parentNode.removeChild(rawTdpOptions) // Remove the DOM element containing the sounds options

    const playlist = []
    Object.keys(tdpOptions).forEach((title, index) => {
      const file = tdpOptions[title]
      if (file) {
        const newTitle = file
          .replace(/^.*[\\/]/, '')
          .replace(/_/g, ' ')
          .replace(/\..*$/, '') // get the real title
        playlist.push({
          newTitle,
          file,
          howl: null
        })
        const active = index === 0 ? 'class="active"' : ''
        if (htmlPlaylist) {
          htmlPlaylist.append(`<input type="button" ${active || ''} value="${newTitle}" />`)
        }
      }
    })

    // Init sound button effect
    const soundBtn = document.getElementById('sound-btn')
    if (playlist.length > 0 && soundBtn) {
      if (htmlPlaylist.length > 0) {
        const player = audio[0]
        soundBtn.onclick = () => {
          if (player.paused) player.play()
          else player.pause()
        }
      } else {
        const player = new Player(playlist, icon => animateSoundBtn(soundBtn, icon))
        soundBtn.onclick = () => {
          if (!player.playing()) player.play()
          else player.pause()
        }
      }
    }

    // Init HTML playlist effect
    if (audio.length > 0) {
      let current = 0
      const tracks = htmlPlaylist.find('input')
      const len = tracks.length

      if (len > 0) {
        audio[0].src = playlist[0].file // Set the first track

        tracks.click(e => {
          e.preventDefault()
          const link = $(e.target)
          current = link.index()
          play(link, audio[0], playlist)
        })

        audio[0].addEventListener('ended', () => {
          current++
          let link
          if (current === len) {
            // current = 0
            // link = tracks[0]
          } else {
            link = tracks[current]
          }
          play($(link), audio[0], playlist)
        })

        audio[0].addEventListener('playing', () => {
          animateSoundBtn(soundBtn, 'speaker')
        })

        audio[0].addEventListener('pause', () => {
          animateSoundBtn(soundBtn, 'speaker-mute')
        })
      }
    }
  }
})(jQuery)
