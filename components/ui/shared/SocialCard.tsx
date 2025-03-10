import Link from "next/link";

const SocialCards = ({ title, icon, url, target }) => {
  return (
    <ul>
      <li>
        <div className='a-icon'>{icon}</div>
        <div className='a-text'>
          <Link
            target={target}
            className='text-reset stretched-link'
            href={`${url}`}
          >
            {title}
          </Link>
        </div>
      </li>
    </ul>
  );
};

export default SocialCards;
