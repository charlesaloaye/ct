'use client'
import Image from "next/image"
import Link from "next/link"
import { menu } from "@/constants/const"
import { useState } from "react";
// import { BiCheck, BiCircleHalf } from "react-icons/bi";
// import { BsFillMoonStarsFill, BsFillSunFill } from "react-icons/bs";

const Header = () => {
    const [isOpen, setIsOpen] = useState(false);
    // const [mode, setMode] = useState('auto');

    // const handleClick = (e :any) => {

    //     e.preventDefault();

    //     localStorage.setItem('theme', e.target.value)

    //     setMode(e.target.value)
    // }
    
  return (
    <>
    <header className="main-header d-lg-none">
    <div className="container">
        <div className="logo">
            <Link className="navbar-brand" href="#">
                <Image height={150} width={150} className="w-full h-auto logo-dark" title={process.env.NEXT_PUBLIC_APP_NAME} alt={`${process.env.NEXT_PUBLIC_APP_NAME}`} src="/assets/img/logo-black.png"/>
                              <Image  height={150} width={150} className="w-full h-auto logo-light" title={process.env.NEXT_PUBLIC_APP_NAME} alt={`${process.env.NEXT_PUBLIC_APP_NAME}`} src="/assets/img/logo-black.png"/>
            </Link>
        </div>
        <div className="ms-auto">
            <button onClick={() => setIsOpen((prevState) => !prevState)} className="toggler-menu">
                <span></span>
            </button>
        </div>
    </div>
</header>
<div className={`${isOpen ? 'menu-open menu-open-desk open header-left-fixed' : 'header-left-fixed'} one-page-nav`}>
        
        <div className="logo">
            <Link className="navbar-brand" href="#">
                <Image height={55} width={120} className="w-full h-auto logo-dark" title={process.env.NEXT_PUBLIC_APP_NAME} alt={`${process.env.NEXT_PUBLIC_APP_NAME}`} src="/assets/img/logo-black.png"/>
                <Image height={55} width={120} className="w-full h-auto logo-light" title={process.env.NEXT_PUBLIC_APP_NAME} alt={`${process.env.NEXT_PUBLIC_APP_NAME}`} src="/assets/img/logo-black.png"/>
            </Link>
        </div>
        
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
                    <button type="button" className="dropdown-item d-flex align-items-center" value={'light'}  onClick={()=>handleClick}>
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
                    <button type="button" className="dropdown-item d-flex align-items-center" value={'dark'}  onClick={()=>handleClick}>
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
                    <button type="button" className="dropdown-item d-flex align-items-center active"  value={'auto'} onClick={()=>handleClick}>
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
        </div> */}
        
    </div>
</>
  )
}

export default Header
