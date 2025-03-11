import { socials } from "@/constants/const";
import Image from "next/image";
import Link from "next/link";

const Footer = () => {

  return (
     <footer className="footer">
    <div className="container">
        <div className="footer-info">
        <div className="footer-avatar">
                <Image className="w-full h-auto" src="/assets/img/charlesaloaye.jpg" title={`${process.env.NEXT_PUBLIC_APP_NAME}`} alt={`${process.env.NEXT_PUBLIC_APP_NAME}`} height={150} width={150}/>
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

  )
}

export default Footer
