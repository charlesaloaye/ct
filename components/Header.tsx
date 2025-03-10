// import Image from "next/image"
// import Link from "next/link"

const Header = () => {
  return (
    <header className="main-header d-lg-none">
    <div className="container">
        {/* <div className="logo">
            <Link className="navbar-brand" href="#">
                <Image height={75} width={75} className="logo-dark" title={process.env.NEXT_PUBLIC_APP_NAME} alt={process.env.NEXT_PUBLIC_APP_NAME} src="/assets/img/logo-black.png"/>
                              <Image height={75} width={75} className="logo-light" title={process.env.NEXT_PUBLIC_APP_NAME} alt={process.env.NEXT_PUBLIC_APP_NAME} src="/assets/img/logo-black.png"/>
            </Link>
        </div> */}
        <div className="ms-auto">
            <button className="toggler-menu">
                <span></span>
            </button>
        </div>
    </div>
</header>
  )
}

export default Header
