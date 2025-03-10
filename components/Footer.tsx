// 'use client'
import { socials } from "@/constants/const";
import Image from "next/image";
import Link from "next/link"
// import { useEffect, useState } from "react";

const Footer = () => {
    
  // const [loading, setLoading] = useState(true);
  // useEffect(() => {
  //   setLoading(false);

  //   if(loading) {
  //     require("bootstrap/dist/js/bootstrap.bundle.min.js");
  //     require(".././public/js/custom.js");
  //     require(".././public/js/dark-light.js");
  //   }

  //   return;
  // }, [loading]);
  return (
<>
     <footer className="footer">
    <div className="container">
        <div className="footer-info">
            <div className="footer-avatar">
                <Image src="/assets/img/mask.jpg" title={`${process.env.NEXT_PUBLIC_APP_NAME}`} alt={`${process.env.NEXT_PUBLIC_APP_NAME}`} height={150} width={150}/>
            </div>
            <h6>{process.env.NEXT_PUBLIC_APP_NAME}</h6>
        </div>
        <ul className="nav social-link">
            {socials.map((social, index) => 
             <li key={index}>
             <Link href={social.url} title={social.title} target={social.target}>{social.icon}</Link>
         </li>
            )}
           
        </ul>
        <p className="copyright">&copy; {new Date().getFullYear()} copyright all right reserved</p>
    </div>
</footer>

{/* <Script src="https://code.jquery.com/jquery-3.6.0.min.js" strategy="beforeInteractive"/>
<Script src="https://code.jquery.com/jquery-3.6.0.min.js" strategy="beforeInteractive"/> */}

</>
  )
}

export default Footer
