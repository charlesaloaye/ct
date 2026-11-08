/** The logo: the uploaded image when there is one, otherwise the letters (e.g. "ct"). */
export default function Mark({ identity, className, style }) {
  return (
    <span className={`${className} ${identity.logoUrl ? 'has-logo' : ''}`} style={style}>
      {identity.logoUrl ? <img src={identity.logoUrl} alt="" /> : identity.mark}
    </span>
  );
}
