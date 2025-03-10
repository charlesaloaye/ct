'use client'
import { menu } from "@/constants/const"
import Image from "next/image"
import Link from "next/link"
import { useState } from "react"
import { BiCheck, BiCircleHalf } from "react-icons/bi"
import { BsFillMoonStarsFill, BsFillSunFill } from "react-icons/bs"

const Menu = () => {
    const [theme, setTheme] = useState('dark');

  return (
    <div className="header-left-fixed one-page-nav">
        
        {/* <div className="logo">
            <Link className="navbar-brand" href="#">
                <Image height={75} width={250} className="logo-dark" title={process.env.NEXT_PUBLIC_APP_NAME} alt={process.env.NEXT_PUBLIC_APP_NAME} src="/assets/img/logo-black.png"/>
                <Image height={75} width={250} className="logo-light" title={process.env.NEXT_PUBLIC_APP_NAME} alt={process.env.NEXT_PUBLIC_APP_NAME} src="/assets/img/logo-black.png"/>
            </Link>
        </div> */}
        
        <ul className="main-menu">

            {menu.map((link, index) => <li key={index}>
                <Link data-scroll-nav="0" href={`${link.url}`} title={link.title}>
                    <span className="m-icon">
                        {link.icon}
                    </span>
                    <span className="m-text">{link.title}</span>
                </Link>
            </li>
 )}
          
        </ul>
        {/* Dark/Light Mode */}
        {/* <div className="theme-btn dropdown">
            <button className="btn dropdown-toggle" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static">
                <span className="bi my-1 theme-icon-active">
                <BiCircleHalf/>
                </span>
            </button>
            <ul className="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme" >
                <li>
                    <button type="button" className="dropdown-item d-flex align-items-center" onClick={(e) => setTheme('light')}>
                        <span className="bi me-2 opacity-50 theme-icon">
                            <BsFillSunFill/>
                        </span>
                        Light
                        <span className="bi ms-auto d-none">
                            <BiCheck/>
                        </span>
                    </button>
                </li>
                <li>
                    <button type="button" className="dropdown-item d-flex align-items-center"  onClick={(e) => setTheme('dark')}>
                        <span className="bi me-2 opacity-50 theme-icon">
                            <BsFillMoonStarsFill/>

                        </span>
                        Dark
                        <span className="bi ms-auto d-none">
                            <BiCheck/>
                        </span>
                    </button>
                </li>
                <li>
                    <button type="button" className="dropdown-item d-flex align-items-center active" onClick={(e) => setTheme('auto')}>
                        <span className="bi me-2 opacity-50 theme-icon">
                            <BiCircleHalf/>
                        </span>
                        Auto
                        <span className="bi ms-auto d-none">
                        <BiCheck/>

                        </span>
                    </button>
                </li>
            </ul>
        </div>
         */}
    </div>
  )
}

export default Menu
