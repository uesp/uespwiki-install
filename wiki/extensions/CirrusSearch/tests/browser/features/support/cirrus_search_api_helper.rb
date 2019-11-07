require "mimemagic"
require "faraday"
require "digest/sha1"

# Common code cirrus' test use when dealing with api.
module CirrusSearchApiHelper
  def commons_api
    @_commons_api ||= MediawikiApi::Client.new(ENV["MEDIAWIKI_COMMONS_API_URL"])
  end

  def log_in_api(current_api = nil)
    current_api ||= api
    current_api.log_in(ENV["MEDIAWIKI_USER"], ENV["MEDIAWIKI_PASSWORD"]) unless current_api.logged_in?
  end

  def edit_page(title, text, add, current_api = nil)
    current_api ||= api
    text = File.read("articles/" + text[1..-1]) if text.start_with?("@")
    fetched_text = get_page_text(title, current_api)
    # Note that the space keeps words from smashing together
    text = fetched_text + " " + text if add
    return if fetched_text.strip == text.strip
    log_in_api current_api
    result = current_api.create_page(title, text)
    expect(result.status).to eq 200
    expect(result.warnings?).to eq false
  end

  # Gets page text using the api.
  def get_page_text(title, current_api = nil)
    current_api ||= api
    fetched_text = current_api.get_wikitext(title)
    return "" if fetched_text.status == 404
    fetched_text.status.should eq 200
    fetched_text.body.strip.force_encoding("utf-8")
  end

  # Search for a particular string using the api
  def search_for(search, options)
    data = api.query(options.merge(
      list: "search",
      srsearch: search,
      srprop: "snippet|titlesnippet|redirectsnippet|sectionsnippet|categorysnippet|isfilematch",
      formatversion: 2
    ))
    data["query"]
  end

  # Get suggestions for a particular string using the api
  def suggestions_for(search, current_api = nil)
    current_api ||= api
    current_api.action(
      :opensearch,
      search: search,
      token_type: false
    )
  end

  # Get suggestions for a particular string using the new suggestions api
  def suggestions_for_api(search, limit = nil)
    req = {}
    req["limit"] = limit if limit
    api.action(:'cirrus-suggest', req.merge(
      text: search,
      token_type: false
    ))
  end

  def sha1_for_image(title, current_api = nil)
    current_api ||= api
    existing = current_api.prop(
      :imageinfo,
      titles: title,
      iiprop: "sha1",
      iilimit: 1
    )
    # api will always return 1 item, if non existent it will have the id -1
    return false if existing.data["pages"].first[0] == "-1"
    existing.data["pages"].first[1]["imageinfo"][0]["sha1"]
  end

  # Uploads a file if the file's SHA1 doesn't match what is already uploaded.
  def upload_file(title, contents, description, current_api = nil)
    contents = "articles/" + contents
    sha1 = Digest::SHA1.hexdigest(File.read(contents))
    remote_sha1 = sha1_for_image(title, current_api)
    return if sha1 == remote_sha1
    do_upload_file(title, contents, description, sha1, remote_sha1 != false, current_api)
  end

  # just uploads the file
  # rubocop:disable ParameterLists
  def do_upload_file(title, contents, description, sha1, ignorewarnings, current_api = nil)
    current_api ||= api
    log_in_api current_api
    current_api.action(
      :upload,
      filename: title,
      file: Faraday::UploadIO.new(contents, MimeMagic.by_path(contents)),
      text: "#{description}\nsha1: #{sha1}",
      # must ignorewarnings to upload new version of a file
      ignorewarnings: ignorewarnings ? 1 : 0,
      token_type: "edit"
    )
  end

  # Locate a category id
  def page_id_of(title)
    res = api.query(
      titles: title
    )
    res["query"]["pages"].first[1]["pageid"]
  end
end
