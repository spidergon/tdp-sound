import Player from './player'

function animateSoundBtn (btn, icon) {
  btn.classList.remove('loading', 'speaker', 'speaker-mute')
  btn.classList.add(icon)
}

function run (link, player) {
  player.src = link.attr('href')
  link
    .addClass('active')
    .siblings()
    .removeClass('active')
  player.load()
  player.play()
}

/* eslint-disable no-extra-semi */
;(function ($) {
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
      title = file.replace(/^.*[\\/]/, '') // get the real title
      playlist.push({
        title,
        file,
        howl: null
      })
      const active = index === 0 ? 'class="active"' : ''
      if (active) {
        audio.append(`<source type="audio/mp3" src="${file}">`)
      }
      if (htmlPlaylist) {
        htmlPlaylist.append(`<a ${active || ''} href="${file}">${title}</a>`)
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
        const player = new Player(playlist, icon =>
          animateSoundBtn(soundBtn, icon)
        )
        soundBtn.onclick = () => {
          if (!player.playing()) player.play()
          else player.pause()
        }
      }
    }

    // Init HTML playlist effect
    if (audio.length > 0) {
      let current = 0
      const tracks = htmlPlaylist.find('a')
      const len = tracks.length

      tracks.click(e => {
        e.preventDefault()
        const link = $(e.target)
        current = link.index()
        run(link, audio[0])
      })

      audio[0].addEventListener('ended', e => {
        current++
        let link
        if (current === len) {
          current = 0
          link = tracks[0]
        } else {
          link = tracks[current]
        }
        run($(link), audio[0])
      })

      audio[0].addEventListener('playing', e => {
        animateSoundBtn(soundBtn, 'speaker')
      })

      audio[0].addEventListener('pause', e => {
        animateSoundBtn(soundBtn, 'speaker-mute')
      })
    }
  }
})(jQuery)
