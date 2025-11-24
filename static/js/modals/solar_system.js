// solar_system.js
export const ORB_TARGETS = {
    'Mercurio': { instance: new Orb.VSOP("Mercury"), type: 'Planeta', nakedEye: true },
    'Venus': { instance: new Orb.VSOP("Venus"), type: 'Planeta', nakedEye: true },
    'Marte': { instance: new Orb.VSOP("Mars"), type: 'Planeta', nakedEye: true },
    'Júpiter': { instance: new Orb.VSOP("Jupiter"), type: 'Planeta', nakedEye: true },
    'Saturno': { instance: new Orb.VSOP("Saturn"), type: 'Planeta', nakedEye: true },
    'Urano': { instance: new Orb.VSOP("Uranus"), type: 'Planeta', nakedEye: false },
    'Neptuno': { instance: new Orb.VSOP("Neptune"), type: 'Planeta', nakedEye: false },
    'Sol': { instance: new Orb.Sun(), type: 'Estrella', nakedEye: true },
    'Luna': { instance: new Orb.Luna(), type: 'Luna', nakedEye: true },
};
